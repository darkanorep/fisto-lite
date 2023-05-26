<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'form_type',
        'name'
    ];

    protected $hidden = [
        'created_at'
    ];

}
