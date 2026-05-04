<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vat extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'vat';

    protected $fillable = [
        'vat',
        'note',
    ];

    protected $casts = [
        'vat' => 'decimal:2',
    ];
}
