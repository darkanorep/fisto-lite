<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
    ];

    protected $fillable = [
        'amount',
        'transaction_id',
        'approved_by',
        'state'
    ];

    public function  transaction(){
        return  $this->belongsTo(Transaction::class);
    }
}
