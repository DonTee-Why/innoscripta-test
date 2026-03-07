<?php

namespace App\Checklist\Rules;

use App\Contracts\CountryChecklistRulesInterface;
use InvalidArgumentException;

class GermanyRules implements CountryChecklistRulesInterface
{
    /**
     * @return array<string, callable(array): bool>
     */
    public function rules(): array
    {
        return [
            'salary' => fn(array $employee) => isset($employee['salary']) && (float) $employee['salary'] > 0,
            'goal' => fn(array $employee) => !empty($employee['goal']),
            'tax_id' => fn(array $employee) => isset($employee['tax_id'])
                && !empty($employee['tax_id'])
                && preg_match('/^DE\d{9}$/', $employee['tax_id']) === 1,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'salary' => 'Salary is required.',
            'goal' => 'Goal is required.',
            'tax_id' => 'Tax ID is required.',
        ];
    }

    public function label(string $key): string
    {
        return match ($key) {
            'salary' => 'Salary',
            'goal' => 'Goal',
            'tax_id' => 'Tax ID',
            default => throw new InvalidArgumentException("Invalid key [{$key}]"),
        };
    }
}
