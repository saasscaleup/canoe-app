<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundRequest extends FormRequest
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
        $currentYear = date('Y');

        return [
            'name' => 'string',
            'start_year' => 'integer|between:1900,' . $currentYear,
            'fund_manager_id' => 'exists:fund_managers,id',
            'aliases' => 'array',
            'aliases.*' => 'string',
            'companies' => 'array',
            'companies.*' => 'exists:companies,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fund_manager_id.exists' => 'The selected fund manager does not exist.',
            'companies.*.exists' => 'The selected companies does not exist.',
        ];
    }
}
