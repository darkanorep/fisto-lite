<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected  $fillable = [
        'code',
        'department',
        'company_id'
    ];

    protected $hidden = [
        'created_at',
    ];

    public function company() {
        return $this->belongsTo(Company::class)->select(['id', 'code', 'company']);
    }
}
