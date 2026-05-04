<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Member extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'members';
    
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'active',
        'b2b_approved',
        'address',
        'post_code',
        'city',
        'country',
        'phone',
        'fax',
        'skey',
        'vat_num',
        'company_reg_num',
        'type',
        'cperson',
        'balance'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function countryInfo()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'member_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'member_id');
    }

    // Scopes for yes/no strings
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    public function scopeB2bApproved($query)
    {
        return $query->where('b2b_approved', 'yes');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->type,
            'source_table' => 'members'
        ];
    }
}
