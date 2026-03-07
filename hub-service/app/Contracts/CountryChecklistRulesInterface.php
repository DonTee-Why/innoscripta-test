<?php

namespace App\Contracts;

interface CountryChecklistRulesInterface
{
    /**
     * @return array<string, callable(array): bool>
     */
    public function rules(): array;

    /**
     * @return array<string, string>
     */
    public function messages(): array;

    /**
     * @param string $key
     * @return string
     */
    public function label(string $key): string;
}
