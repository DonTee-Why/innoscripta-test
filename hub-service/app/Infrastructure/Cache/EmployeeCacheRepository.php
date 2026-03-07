<?php

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class EmployeeCacheRepository
{
    private const EMPLOYEE_HASH_PREFIX = 'hub:employees';
    private const EMPLOYEE_LIST_CACHE_PREFIX = 'hub:employees:list';
    private const EMPLOYEE_LIST_REGISTRY_PREFIX = 'hub:employees:list:keys';

    /**
     * Store or update a single employee payload in a country-specific Redis hash.
     *
     * @param array<string, mixed> $employee
     */
    public function upsert(string $country, int $employeeId, array $employee): void
    {
        Redis::command('hset', [
            $this->employeeHashKey($country),
            (string) $employeeId,
            json_encode($employee, JSON_UNESCAPED_SLASHES)
        ]);
    }

    /**
     * Remove one employee entry from the country-specific Redis hash.
     */
    public function delete(string $country, int $employeeId): void
    {
        Redis::command('hdel', [
            $this->employeeHashKey($country),
            (string) $employeeId
        ]);
    }

    /**
     * Fetch and decode all employees for a given country.
     *
     * @return array<int|string, array<string, mixed>|null>
     */
    public function all(string $country): array
    {
        $employees = Redis::command('hgetall', [
            $this->employeeHashKey($country)
        ]);

        if (empty($employees)) {
            return [];
        }

        return array_map(
            fn ($employee) => json_decode($employee, true),
            $employees
        );
    }

    /**
     * Fetch a single employee by country and ID.
     *
     * @return array<string, mixed>|null
     */
    public function find(string $country, int $employeeId): ?array
    {
        $employee = Redis::command('hget', [
            $this->employeeHashKey($country),
            (string) $employeeId
        ]);

        if ($employee === null || $employee === false) {
            return null;
        }

        return json_decode($employee, true);
    }

    /**
     * Return a paginated employee payload, cached per country/page/per-page.
     *
     * @return array{
     *     data: array<array<string, mixed>|null>,
     *     meta: array{page: int, per_page: int, total: int, last_page: int}
     * }
     */
    public function paginate(string $country, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $cacheKey = $this->employeeListCacheKey($country, $page, $perPage);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $employees = array_values($this->all($country));

        $total = \count($employees);
        $offset = ($page - 1) * $perPage;

        $items = \array_slice($employees, $offset, $perPage);

        $payload = [
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];

        Cache::put($cacheKey, $payload, now()->addMinutes(10));

        Redis::command('sadd', [
            $this->employeeListRegistryKey($country),
            $cacheKey
        ]);

        return $payload;
    }

    /**
     * Invalidate all cached paginated lists registered for a country.
     * @param string $country
     * @return void
     */
    public function invalidatePaginatedLists(string $country): void
    {
        $registryKey = $this->employeeListRegistryKey($country);

        $keys = Redis::command('smembers', [$registryKey]);

        if (!empty($keys)) {
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }

        Redis::command('del', [$registryKey]);
    }

    /**
     * Remove all employee hash data and related paginated cache entries for a country.
     * @param string $country
     * @return void
     */
    public function flushCountry(string $country): void
    {
        Redis::command('del', [
            $this->employeeHashKey($country)
        ]);

        $this->invalidatePaginatedLists($country);
    }

    /**
     * Compute the Redis hash key for a country.
     * @param string $country
     * @return string
     */
    private function employeeHashKey(string $country): string
    {
        return self::EMPLOYEE_HASH_PREFIX . ':' . $country;
    }

    /**
     * Compute the Redis list cache key for a country.
     * @param string $country
     * @param int $page
     * @param int $perPage
     * @return string
     */
    private function employeeListCacheKey(string $country, int $page, int $perPage): string
    {
        return self::EMPLOYEE_LIST_CACHE_PREFIX . ':' . $country . ':' . $page . ':' . $perPage;
    }

    /**
     * Compute the Redis list registry key for a country.
     * @param string $country
     * @return string
     */
    private function employeeListRegistryKey(string $country): string
    {
        return self::EMPLOYEE_LIST_REGISTRY_PREFIX . ':' . $country;
    }
}
