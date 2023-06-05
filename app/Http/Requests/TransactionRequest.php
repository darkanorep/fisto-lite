<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
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
            'document_no' => ['required', 'string', Rule::unique('transactions', 'document_no')->ignore($this->transaction)],
            'document_date' => 'required|date|before:today',
            'document_amount' => 'required|numeric',
            'company_id' => 'required|string|exists:companies,id',
            'location_id' => 'required|string|exists:locations,id',
            'supplier_id' => 'required|string|exists:suppliers,id',
            'remarks' => 'nullable|string',
            'po_group' => 'required_if:document_id,1',
            'po_group.*.po_number' => [
                'required_if:document_id,1',
                Rule::unique('p_o_batches', 'po_number')->where(function ($query) {
                    $query->where('po_number', $this->input('po_group.*.po_number'))
                    ->where('transaction_id', '!=', $this->transaction);;
                }),
                'distinct'
            ],
            'po_group.*.po_amount' => 'required_if:document_id,1',
            'po_group.*.rr_no' => 'nullable',
        ];
    }

    public function messages()
    {

        return [
            'document_id.exists' => 'Document not found.',
            'po_group.*.po_number.distinct' => 'PO Number already in list.',
            'po_group.required_if' => 'PO field is required when document type is '. Document::where('id', $this->document_id)->first()->type,
        ];
    }
}
