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
     * Prepare the data for validation (merge with existing employee for partial updates).
     */
    protected function prepareForValidation(): void
    {
        $employee = $this->route('employee');

        $merge = [];
        if (! $this->has('name')) {
            $merge['name'] = $employee->name;
        }
        if (! $this->has('last_name')) {
            $merge['last_name'] = $employee->last_name;
        }
        if (! $this->has('country')) {
            $merge['country'] = $employee->country;
        }
        if (! $this->has('salary')) {
            $merge['salary'] = $employee->salary;
        }
        if (! $this->has('country')) {
            if (! $this->has('ssn')) {
                $merge['ssn'] = $employee->ssn;
            }
            if (! $this->has('address')) {
                $merge['address'] = $employee->address;
            }
            if (! $this->has('tax_id')) {
                $merge['tax_id'] = $employee->tax_id;
            }
            if (! $this->has('goal')) {
                $merge['goal'] = $employee->goal;
            }
        }
        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', Rule::in(Country::toArray())],
            'salary' => ['required', 'numeric', 'gt:0'],
            'ssn' => ['required_if:country,USA', 'nullable', 'string', 'max:255'],
            'address' => ['required_if:country,USA', 'nullable', 'string', 'max:255'],
            'tax_id' => ['required_if:country,Germany', 'nullable', 'string', 'max:255'],
            'goal' => ['required_if:country,Germany', 'nullable', 'string', 'max:1000'],
        ];
    }
}
