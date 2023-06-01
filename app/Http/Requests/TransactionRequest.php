<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'document_id' => 'required|string|exists:documents,id',
            'category_id' => 'required|string|exists:categories,id',
            'document_no' => 'required|string|unique:transactions,document_no',
            'document_date' => 'required|date|before:today',
            'document_amount' => 'required|numeric',
            'company_id' => 'required|string|exists:companies,id',
            'location_id' => 'required|string|exists:locations,id',
            'supplier_id' => 'required|string|exists:suppliers,id',
            'remarks' => 'nullable|string',
            'po_group' => 'required|array',
            'po_group.*.po_number' => 'required',
            'po_group.*.po_amount' => 'required',
            'po_group.*.rr_no' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'document_id.exists' => 'Document not found.',
        ];
    }
}
