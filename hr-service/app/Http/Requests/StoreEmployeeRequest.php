<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'salary' => 'required_if:country,USA,Germany|numeric',
            'ssn' => 'required_if:country,USA|string|max:255',
            'address' => 'required_if:country,USA|string|max:255',
            'tax_id' => 'required_if:country,Germany|string|max:255',
            'goal' => 'required_if:country,Germany|string|max:1000',
        ];
    }
}
