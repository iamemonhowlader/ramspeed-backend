<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $table = 'newsletter';
    protected $primaryKey = 'id';

    protected $fillable = [
        'Email',
        'Phone_Number'
    ];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // For compatibility with the legacy database structure
    public function getEmailAttribute()
    {
        return $this->attributes['Email'];
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['Email'] = $value;
    }

    public function getPhoneAttribute()
    {
        return $this->attributes['Phone_Number'];
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['Phone_Number'] = $value;
    }
}
