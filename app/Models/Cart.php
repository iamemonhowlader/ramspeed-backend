<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cart';

    protected $fillable = [
        'product_id',
        'member_id',
        'supplier_id',
        'quantity',
        'store_type',
        'price',
    ];

    protected $casts = [
        'product_id'  => 'integer',
        'member_id'   => 'integer',
        'supplier_id' => 'integer',
        'quantity'    => 'integer',
        'store_type'  => 'integer',
        'price'       => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
