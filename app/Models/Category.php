<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'namegr',
        'description',
        'subscriber_count',
        'active',
    ];

    protected $casts = [
        'subscriber_count' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subscribers()
    {
        return $this->hasMany(CategorySubscriber::class, 'category_id');
    }

    // Scope for active categories (DB stores 'yes'/'no')
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }
}
