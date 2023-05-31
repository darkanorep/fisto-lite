<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
            'code' => ['required', 'string', Rule::unique('suppliers', 'code')->ignore($this->supplier)],
            'name' => 'required|string',
            'terms' => 'required|string',
            'urgency_type_id' => 'required|exists:urgency_types,id',
            'references.*' => ['required', 'exists:references,id']
        ];
    }

    public function attributes()
    {
        return [
            'code' => 'supplier code',
            'urgency_type_id' => 'urgency type'
        ];
    }

    public function messages()
    {
        return [
            'references.*.exists' => 'The ID :input for references does not exist.',
            'urgency_type_id.exists' => 'The ID :input urgency type does not exist'
        ];
    }
}
