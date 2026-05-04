<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class AdminUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'admin_users';
    public $timestamps = false;

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
    ];

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

    /**
     * The supplier's extended info stored in suppliers_info table.
     * Linked via suppliers_info.user_id = admin_users.id
     */
    public function supplierInfo()
    {
        return $this->hasOne(Supplier::class, 'user_id');
    }
}
