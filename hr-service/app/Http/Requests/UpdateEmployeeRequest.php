<?php

namespace App\Http\Requests;

use App\Enums\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'country' => ['sometimes', 'required', 'string', Rule::in(Country::toArray())],
            'salary' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'ssn' => ['required_if:country,USA', 'nullable', 'string', 'max:255'],
            'address' => ['required_if:country,USA', 'nullable', 'string', 'max:255'],
            'tax_id' => ['required_if:country,Germany', 'nullable', 'string', 'max:255', 'regex:/^DE\d{9}$/'],
            'goal' => ['required_if:country,Germany', 'nullable', 'string', 'max:1000'],
        ];
    }
}
