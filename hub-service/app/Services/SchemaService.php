<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Country;
use InvalidArgumentException;

final class SchemaService
{
    public function getByStepAndCountry(string $stepId, string $country): array
    {
        $normalizedStepId = strtolower($stepId);

        return [
            'country' => $country,
            'step_id' => $normalizedStepId,
            'widgets' => match ($normalizedStepId) {
                'dashboard' => $this->dashboardWidgets($country),
                'employees' => $this->employeesWidgets($country),
                'documentation' => $this->documentationWidgets($country),
                default => throw new InvalidArgumentException("Unsupported step [{$stepId}]"),
            },
        ];
    }

    private function dashboardWidgets(string $country): array
    {
        return match ($country) {
            Country::USA => [
                [
                    'id' => 'employee_count',
                    'type' => 'stat',
                    'title' => 'Employee Count',
                    'data_source' => '/api/employees?country=USA',
                    'realtime' => [
                        'channel' => 'country.usa.employees',
                        'event' => 'employee.updated',
                    ],
                ],
                [
                    'id' => 'average_salary',
                    'type' => 'stat',
                    'title' => 'Average Salary',
                    'data_source' => '/api/employees?country=USA',
                    'realtime' => [
                        'channel' => 'country.usa.employees',
                        'event' => 'employee.updated',
                    ],
                ],
                [
                    'id' => 'completion_rate',
                    'type' => 'stat',
                    'title' => 'Completion Rate',
                    'data_source' => '/api/checklists?country=USA',
                    'realtime' => [
                        'channel' => 'country.usa.checklists',
                        'event' => 'checklist.updated',
                    ],
                ],
            ],

            Country::Germany => [
                [
                    'id' => 'employee_count',
                    'type' => 'stat',
                    'title' => 'Employee Count',
                    'data_source' => '/api/employees?country=Germany',
                    'realtime' => [
                        'channel' => 'country.germany.employees',
                        'event' => 'employee.updated',
                    ],
                ],
                [
                    'id' => 'goal_tracking',
                    'type' => 'list-summary',
                    'title' => 'Goal Tracking',
                    'data_source' => '/api/employees?country=Germany',
                    'realtime' => [
                        'channel' => 'country.germany.employees',
                        'event' => 'employee.updated',
                    ],
                ],
            ],

            default => throw new InvalidArgumentException("Unsupported country [{$country}]"),
        };
    }

    private function employeesWidgets(string $country): array
    {
        return [
            [
                'id' => 'employees_table',
                'type' => 'table',
                'title' => 'Employees',
                'data_source' => "/api/employees?country={$country}",
                'realtime' => [
                    'channel' => 'country.' . strtolower($country) . '.employees',
                    'event' => 'employee.updated',
                ],
            ],
        ];
    }

    private function documentationWidgets(string $country): array
    {
        if ($country !== Country::Germany) {
            return [];
        }

        return [
            [
                'id' => 'documentation_panel',
                'type' => 'panel',
                'title' => 'Documentation',
                'data_source' => null,
                'realtime' => [
                    'channel' => null,
                    'event' => null,
                ],
            ],
        ];
    }
}
