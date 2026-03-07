<?php

namespace App\Checklist\Rules;

use App\Contracts\CountryChecklistRulesInterface;
use InvalidArgumentException;

class UsaRules implements CountryChecklistRulesInterface
{
    /**
     * @return array<string, callable(array): bool>
     */
    public function rules(): array
    {
        return [
            'ssn' => fn(array $employee) => isset($employee['ssn']) && !empty($employee['ssn']),
            'salary' => fn(array $employee) => isset($employee['salary']) && $employee['salary'] > 0,
            'address' => fn(array $employee) => isset($employee['address']) && !empty($employee['address']),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ssn' => 'SSN is required.',
            'salary' => 'Salary is required.',
            'address' => 'Address is required.',
        ];
    }

    /**
     * @param string $key
     * @return string
     */
    public function label(string $key): string
    {
        return match ($key) {
            'ssn' => 'SSN',
            'salary' => 'Salary',
            'address' => 'Address',
            default => throw new InvalidArgumentException("Invalid key [{$key}]"),
        };
    }
}
