<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationalDepartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at',
    ];

    protected $fillable = [
        'code',
        'name'
    ];
}
