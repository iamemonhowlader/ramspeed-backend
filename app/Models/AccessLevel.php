<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLevel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'access_level';

    protected $fillable = [
        'uid',
        'cpanel',
        'users',
        'orders',
        'products',
        'categories',
        'suppliers',
        'shipping',
        'content',
        'gallery',
        'settings'
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'uid');
    }
}
