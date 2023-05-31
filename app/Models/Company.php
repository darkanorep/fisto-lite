<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    // public function getValidationRules($companyId = null)
    // {
    //     $rules = [
    //         'code' => ['required', 'string', Rule::unique('companies', 'code')->ignore($companyId)],
    //         'company' => 'required|string',
    //         'associates' => 'required|array',
    //     ];

    //     return $rules;
    // }
}
