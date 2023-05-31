<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Company;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    // public function rules(): array
    // {
    //     return [
    //         'code' => ['required', 'string', Rule::unique('companies', 'code')->ignore($this->company)],
    //         'company' => 'required|string',
    //         'associates' => 'required|array',
    //     ];
    // }

    public function rules(): array
    {
        // $companyId = $this->route('company');

        // return (new Company)->getValidationRules($companyId);

        return [
            'code' => ['required', 'string', Rule::unique('companies', 'code')->ignore($this->route('company'))],
            'company' => 'required|string',
            'associates' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $inputIds = collect($value);
                    $existingIds = User::whereIn('id', $inputIds)->pluck('id');

                    $nonExistingIds = $inputIds->diff($existingIds);

                    if (count($nonExistingIds)) {
                        $fail("The selected associate with ID {$nonExistingIds->implode(', ')} does not exist.");
                    }
                }
            ]
        ];
    }
}
