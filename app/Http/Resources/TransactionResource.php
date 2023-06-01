<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->users->id,
                'first_name' => $this->users->first_name,
                'last_name' => $this->users->last_name,
                'role' => $this->users->role,
            ],
            'document' => [
                'id' => $this->documents->id,
                'type' => $this->documents->type,
                'description' => $this->documents->description,
            ],
            'category' => [
                'id' => $this->categories->id,
                'name' => $this->categories->name,
            ],
            'document_no' => $this->document_no,
            'document_amount' => $this->document_amount,
            'document_date' => $this->document_date,
            'company' => [
                'id' => $this->companies->id,
                'code' => $this->companies->code,
                'company' =>  $this->companies->company
            ],
            'location' => [
                'id' => $this->locations->id,
                'code' => $this->locations->code,
                'location' => $this->locations->location
            ],
            'supplier' => [
                'id' => $this->suppliers->id,
                'code' => $this->suppliers->code,
                'terms' => $this->suppliers->terms
            ],
            'remarks' => $this->remarks,
            'status' => $this->status,
            'state' => $this->state,
            'is_received' => $this->is_received
        ];
    }
}
