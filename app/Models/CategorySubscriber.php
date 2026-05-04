<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorySubscriber extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'categories_subscribers';

    protected $fillable = [
        'category_id',
        'email'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
