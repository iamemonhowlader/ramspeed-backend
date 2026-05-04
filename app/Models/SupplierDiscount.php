<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDiscount extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'supplier_discount';

    protected $fillable = [
        'supplier_id',
        'discount_percentage'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
