<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Document;
use App\Models\Supplier;
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
            'category_id' => 'required_if:document_id,1,2,5|string|exists:categories,id',
            'document_no' => ['required_if:document_id,1,2', 'string', Rule::unique('transactions', 'document_no')->ignore($this->transaction)],
            'document_date' => 'required_if:document_id,1,2,5|date|before:today',
            'document_amount' => 'required|numeric',
            // 'company_id' => 'required|string|exists:companies,id',
            'company_id' => [
                'required', 'string', Rule::exists('companies', 'id')->where(function ($query) {
                    $query->where('id', $this->company_id);
                }), 
                function ($attribute, $value, $fail) {
                    $notExist = Company::whereNull('deleted_at')->where('id', $value)->doesntExist();

                    if ($notExist) {
                        $fail("The company does not exist.");
                    }
                }
            ],
            'location_id' => 'required|string|exists:locations,id',
            // 'supplier_id' => 'required|string|exists:suppliers,id', // the exist: validation is for existing even its soft deleted so not recommended
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(function ($query) {
                    $query->where('id', $this->supplier_id);
                }),
                function ($attribute, $value, $fail) {
                    $notExist = Supplier::whereNull('deleted_at')->where('id', $value)->doesntExist();

                    if ($notExist) {
                        $fail("The supplier does not exist.");
                    }
                }
            ],
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
            'po_group.*.po_amount' => 'required_if:document_id,1,5',
            'po_group.*.rr_no' => 'nullable',

            'capex' => ['required_if:document_id,5', 'string', Rule::unique('transactions', 'capex')->ignore($this->transaction)],

            'from_date' => ['required_if:document_id,7', Rule::unique('transactions', 'from_date')->ignore($this->transaction)],
            'to_date' => ['required_if:document_id,7', Rule::unique('transactions', 'to_date')->ignore($this->transaction)],
        ];
    }

    public function messages()
    {

        return [
            'document_id.exists' => 'Document not found.',
            'po_group.*.po_number.distinct' => 'PO Number already in list.',
            'po_group.required_if' => 'PO field is required when document type is ' . Document::where('id', $this->document_id)->first()->type,
            'capex.required_if' => 'Capex field is required when document type is ' . Document::where('id', $this->document_id)->first()->type,
            'from_date.required_if' => 'From date field is required when document type is ' . Document::where('id', $this->document_id)->first()->type,
            'to_date.required_if' => 'To date field is required when document type is ' . Document::where('id', $this->document_id)->first()->type,
        ];
    }
}
