<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_location',
        'ad_link',
        'image_path',
        'active'
    ];

    protected $casts = [
        'active' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('ad_location', $location);
    }
}
