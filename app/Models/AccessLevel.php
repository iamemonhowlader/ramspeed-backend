<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLevel extends Model
{
    protected $table = 'access_level';
    public $timestamps = false;

    protected $fillable = [
        'uid',
        'menu',
        'featured',
        'news',
        'shipping',
        'banlist',
        'user_account',
        'user_level',
        'members',
        'suppliers',
        'BalanceSheet'
    ];

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'uid');
    }
}
