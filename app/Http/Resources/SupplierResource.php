<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'terms' => $this->terms,
            'urgency_types' => [
                'id' => $this->urgencyType->id,
                'type' => $this->urgencyType->type,
                'transaction_days' => $this->urgencyType->transaction_days
            ],
            'references' => $this->references->map(function ($reference) {
                return [
                    'type' => $reference->type,
                    'description' => $reference->description
                ];
            }),
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

}
