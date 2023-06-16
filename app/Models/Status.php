<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status';

    protected $fillable = [
        'transaction_id',
        'is_received',
        'is_returned',
        'is_ap_tag_approved',
        'is_ap_assoc_approved'
    ];
}
