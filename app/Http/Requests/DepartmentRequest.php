<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
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
    public function rules(): array
    {
        if ($_SERVER["REQUEST_URI"] === "/api/admin/department/import") {

            return [
                '*.code' => ['required', Rule::unique('departments', 'code')->ignore($this->department)],
                '*.department' => 'required|string',
                '*.company' => 'required|string|exists:companies,company'
            ];
        }

        return [
            'code' => ['required', 'string', Rule::unique('departments', 'code')->ignore($this->route('department'))],
            'department' => 'required|string',
            'company_id' => 'required|exists:companies,id'
        ];
    }

    public function messages(): array
    {
        if ($_SERVER["REQUEST_URI"] === "/api/admin/department/import") {

            return [
                '*.code.unique' => 'code :input already exists in row :index.',
                '*.company.exists' => ':input is not registered in row :index.',
            ];
        }

        return [
            'company_id.exists' => 'Company not exists.',
        ];
    }
}
