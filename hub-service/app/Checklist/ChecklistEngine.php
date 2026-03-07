<?php

namespace App\Checklist;

class ChecklistEngine
{
    public function __construct(
        private readonly ChecklistRulesFactory $factory
    ) {}

    /**
     * @param array<int, array<string, mixed>> $employees
     */
    public function evaluate(string $country, array $employees): array
    {
        $rule = $this->factory->make($country);
        $results = [];

        foreach ($employees as $employee) {
            $checks = [];
            $completedFields = [];
            $missingFields = [];

            foreach ($rule->rules() as $field => $ruleCallback) {
                $isComplete = $ruleCallback($employee);

                $checks[$field] = [
                    'complete' => $isComplete,
                    'message' => $isComplete
                        ? $rule->label($field) . ' is complete.'
                        : ($rule->messages()[$field]),
                ];

                if ($isComplete) {
                    $completedFields[] = $field;
                } else {
                    $missingFields[] = $field;
                }
            }

            $totalChecks = \count($checks);
            $completedChecks = \count($completedFields);

            $completionPercentage = $totalChecks > 0
                ? (int) round(($completedChecks / $totalChecks) * 100)
                : 0;

            $results[] = [
                'employee_id' => $employee['id'] ?? null,
                'employee' => $employee,
                'checks' => $checks,
                'completed_fields' => $completedFields,
                'missing_fields' => $missingFields,
                'completion_percentage' => $completionPercentage,
                'is_complete' => $completedChecks === $totalChecks,
            ];
        }

        return $this->aggregate($country, $results);
    }

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private function aggregate(string $country, array $results): array
    {
        $totalEmployees = \count($results);

        $fullyCompleteCount = \count(array_filter(
            $results,
            fn(array $result) => $result['is_complete'] === true
        ));

        $averageCompletion = $totalEmployees > 0
            ? (int) round(array_sum(
                array_column($results, 'completion_percentage')
            ) / $totalEmployees)
            : 0;

        return [
            'country' => $country,
            'summary' => [
                'total_employees' => $totalEmployees,
                'fully_complete_employees' => $fullyCompleteCount,
                'incomplete_employees' => $totalEmployees - $fullyCompleteCount,
                'average_completion_percentage' => $averageCompletion,
            ],
            'employees' => $results,
        ];
    }
}
