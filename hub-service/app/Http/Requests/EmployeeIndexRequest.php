<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class EmployeeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', Rule::in(Country::toArray())],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function getCountry(): string
    {
        return $this->validated('country');
    }

    public function getPage(): int
    {
        return (int) ($this->validated('page') ?? 1);
    }

    public function getPerPage(): int
    {
        return (int) ($this->validated('per_page') ?? 15);
    }
}
