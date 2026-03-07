<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ChecklistIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', Rule::in(Country::toArray())],
        ];
    }

    public function getCountry(): string
    {
        return $this->validated('country');
    }
}
