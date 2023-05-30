<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at'
    ];

    protected $fillable = [
        'code',
        'company'
    ];

    public function associates() {
        return $this->belongsToMany(User::class, 'company_user',  'company_id', 'user_id')->select(['users.id', 'users.username', 'users.role'])->withTimestamps();
    }
}
