<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
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
            'type' => ['required','string', Rule::unique('documents', 'type')->ignore($this->document)],
            'description' => 'required|string',
            'categories' => [
                'required', 
                'array',
                function ($attribute, $value, $fail) {
                    $inputIds = collect($value);
                    $existingIds = Category::whereIn('id', $inputIds)->pluck('id');

                    $nonExistingIds = $inputIds->diff($existingIds);

                    if (count($nonExistingIds)) {
                        $fail("The selected categories with ID {$nonExistingIds->implode(', ')} does not exist.");
                    }
                }]
        ];
    }

    public function messages(): array
    {
        return [
            'type.unique' => 'Document type already exists.'
        ];
    }
}
