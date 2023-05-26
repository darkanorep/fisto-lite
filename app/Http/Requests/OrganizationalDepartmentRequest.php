<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Request;

class OrganizationalDepartmentRequest extends FormRequest
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
        if ($_SERVER["REQUEST_URI"] === "/api/admin/organizational-department/import") {

            return [
                'code.*.' => ['required', Rule::unique('organizational_departments', 'code')->ignore($this->organizational_department)],
                'name.*.' => 'required',
            ];

        } elseif (strpos($_SERVER["REQUEST_URI"], "/api/admin/organizational-department/") === 0) {
            $id = substr($_SERVER["REQUEST_URI"], strlen("/api/admin/organizational-department/"));
            
            return [
                'code' => ['required', Rule::unique('organizational_departments', 'code')->ignore($id)],
                'name' => 'required',
            ];
        }

        return [
            'code' => 'required|unique:organizational_departments,code',
            'name' => 'required',
        ];
    }

    public function messages()
    {
       return [
        'code.unique' => 'code :input already registered.'
       ];
    }

    public function attributes()
    {
        return [
            'code' => 'organizational department code',
            'name' => 'organizational department name'
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         $validator->errors()->add("route", $this->route());
    //         $validator->errors()->add("request", $this->request->all());
    //     });
    // }
}
