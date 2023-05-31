<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at',
        
    ];

    protected $fillable = [
        'code',
        'location'
    ];

    public function departments() {
        return $this->belongsToMany(Department::class, 'department_location', 'department_id',  'location_id')->select('departments.id', 'departments.code', 'departments.department as name')->withTimestamps();
    }
}
