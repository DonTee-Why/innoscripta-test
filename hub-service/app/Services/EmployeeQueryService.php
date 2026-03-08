<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Country;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use InvalidArgumentException;

class EmployeeQueryService
{
    public function __construct(
        private readonly EmployeeCacheRepository $employeeCacheRepository,
    ) {}

    /**
     * @param string $country
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByCountry(string $country, int $page = 1, int $perPage = 15): array
    {
        $paginated = $this->employeeCacheRepository->paginate(
            country: $country,
            page: $page,
            perPage: $perPage,
        );

        return [
            'country' => $country,
            'columns' => $this->columnsForCountry($country),
            'data' => $this->transformEmployees($country, $paginated['data']),
            'meta' => $paginated['meta'],
        ];
    }

    /**
     * @param string $country
     * @return array
     */
    private function columnsForCountry(string $country): array
    {
        return match ($country) {
            Country::USA => [
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                ],
                [
                    'key' => 'last_name',
                    'label' => 'Last Name',
                    'type' => 'text',
                ],
                [
                    'key' => 'salary',
                    'label' => 'Salary',
                    'type' => 'currency',
                ],
                [
                    'key' => 'ssn',
                    'label' => 'SSN',
                    'type' => 'masked-text',
                ],
            ],

            Country::Germany => [
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                ],
                [
                    'key' => 'last_name',
                    'label' => 'Last Name',
                    'type' => 'text',
                ],
                [
                    'key' => 'salary',
                    'label' => 'Salary',
                    'type' => 'currency',
                ],
                [
                    'key' => 'goal',
                    'label' => 'Goal',
                    'type' => 'text',
                ],
            ],

            default => throw new InvalidArgumentException("Unsupported country [{$country}]"),
        };
    }

    /**
     * @param string $country
     * @param array $employees
     * @return array
     */
    private function transformEmployees(string $country, array $employees): array
    {
        return array_map(fn(array $employee) => match ($country) {
                Country::USA => [
                    'id' => $employee['id'] ?? null,
                    'name' => $employee['name'] ?? null,
                    'last_name' => $employee['last_name'] ?? null,
                    'salary' => $employee['salary'] ?? null,
                    'ssn' => $this->maskSsn($employee['ssn'] ?? null),
                    'country' => $employee['country'] ?? null,
                ],

                Country::Germany => [
                    'id' => $employee['id'] ?? null,
                    'name' => $employee['name'] ?? null,
                    'last_name' => $employee['last_name'] ?? null,
                    'salary' => $employee['salary'] ?? null,
                    'goal' => $employee['goal'] ?? null,
                    'country' => $employee['country'] ?? null,
            ],

            default => throw new InvalidArgumentException("Unsupported country [{$country}]"),
        }, $employees);
    }

    /**
     * @param null|string $ssn
     * @return null|string
     */
    private function maskSsn(null|string $ssn): ?string
    {
        if (!is_string($ssn) || trim($ssn) === '') {
            return null;
        }

        $lastFour = substr(preg_replace('/\D/', '', $ssn), -4);

        if ($lastFour === false || $lastFour === '') {
            return '***-**-****';
        }

        return "***-**-{$lastFour}";
    }
}