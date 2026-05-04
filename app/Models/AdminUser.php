<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class AdminUser extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'admin_users';
    
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'active',
        'user_type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function accessLevel()
    {
        return $this->hasOne(AccessLevel::class, 'uid');
    }

    public function userLogs()
    {
        return $this->hasMany(UserLog::class, 'username', 'username');
    }

    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->user_type,
            'source_table' => 'admin_users'
        ];
    }
}
