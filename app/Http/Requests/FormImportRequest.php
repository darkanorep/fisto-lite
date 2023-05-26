<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormImportRequest extends FormRequest
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
        return [
            '*.form_type' => 'required',
            '*.name' => 'required',
        ];
    }

    public function messages()
    {
        return  [
            '*.name.distinct' => ':input name duplicated.',
            '*.form_type.exists' => 'ID :input is not registered.',
            '*.name.unique' => ':input is already registered.',
            '*.name.required' => 'Name is required.',
            '*.form_type.required' => 'Form Type is requireds.',
        ];
    
    }
}
