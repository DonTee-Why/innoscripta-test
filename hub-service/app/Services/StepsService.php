<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Country;
use InvalidArgumentException;

final class StepsService
{
    public function getByCountry(string $country): array
    {
        return [
            'country' => $country,
            'steps' => match ($country) {
                Country::USA => [
                    [
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'layout-dashboard',
                        'order' => 1,
                        'path' => '/dashboard',
                    ],
                    [
                        'id' => 'employees',
                        'label' => 'Employees',
                        'icon' => 'users',
                        'order' => 2,
                        'path' => '/employees',
                    ],
                ],

                Country::Germany => [
                    [
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'layout-dashboard',
                        'order' => 1,
                        'path' => '/dashboard',
                    ],
                    [
                        'id' => 'employees',
                        'label' => 'Employees',
                        'icon' => 'users',
                        'order' => 2,
                        'path' => '/employees',
                    ],
                    [
                        'id' => 'documentation',
                        'label' => 'Documentation',
                        'icon' => 'file-text',
                        'order' => 3,
                        'path' => '/documentation',
                    ],
                ],

                default => throw new InvalidArgumentException("Unsupported country [{$country}]"),
            },
        ];
    }
}
