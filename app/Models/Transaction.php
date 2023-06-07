<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Document;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\POBatches;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at'
    ];

    protected $fillable = [
        'user_id',
        'document_id',
        'category_id',
        'document_no',
        'request_date',
        'document_date',
        'document_amount',
        'company_id',
        'location_id',
        'supplier_id',
        'remarks',
        'capex',
        'from_date',
        'to_date',
        'department_id'
    ];

    public function users() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents() {
        return $this->belongsTo(Document::class,  'document_id');
    }

    public function categories() {
        return $this->belongsTo(Category::class,  'category_id');
    }

    public function departments() {
        return $this->belongsTo(Department::class,  'department_id');
    }

    public function companies() {
        return $this->belongsTo(Company::class,   'company_id');
    }

    public function locations() {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function suppliers() {
        return $this->belongsTo(Supplier::class,  'supplier_id');
    }

    public function poBatches() {
        return $this->hasMany(POBatches::class, 'transaction_id');
    }
}
