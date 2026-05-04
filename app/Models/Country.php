<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'countries';
    
    protected $fillable = [
        'name',
        'code',
        'active'
    ];

    public function members()
    {
        return $this->hasMany(Member::class, 'country');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'country');
    }

    // Scope for active countries (yes/no strings)
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }
}
