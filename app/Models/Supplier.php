<?php

namespace App\Models;

use App\Models\UrgencyTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at',
        'urgency_type_id'
    ];

    protected $fillable = [
        'code',
        'name',
        'terms',
        'urgency_type_id'
    ];

    public function urgencyType() {
        return $this->belongsTo(UrgencyTypes::class, 'urgency_type_id')->select('id', 'type', 'transaction_days');
    }

    public function references() {
        return $this->belongsToMany(Reference::class, 'supplier_reference', 'supplier_id', 'reference_id')->select('type', 'description');
    }
}
