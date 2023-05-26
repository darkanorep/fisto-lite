<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'requestor_id'
    ];

    protected $fillable = [
        'form_id',
        'is_ap_approved',
        'status',
        'requestor_id',
        'tag_no'
    ];

    public function voucher() {
        return $this->hasOne(Voucher::class);
    }

    public function form() {
        return $this->belongsTo(Form::class);
    }

    public function requestor() {
        return $this->belongsTo(User::class);
    }
}
