<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UrgencyTypeRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::unique('urgency_types')->ignore($this->urgency_type)],
            'transaction_days' => 'required|numeric'
        ];
    }

    public function attributes()
    {
        return [
            'type' => 'urgency type'
        ];
    }
}
