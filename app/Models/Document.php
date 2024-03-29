<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at'
    ];

    protected $fillable = [
        'type',
        'description'
    ];

    public function categories() {
        return $this->belongsToMany(Category::class, 'document_category', 'document_id', 'category_id')->select(['categories.id', 'categories.name'])->withTimestamps();
    }
}
