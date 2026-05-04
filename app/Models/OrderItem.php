<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'store_type',
        'product_id',
        'temp_id',
        'quantity',
        'price',
        'price_euro',
        'discount',
        'options_msg',
        'temp_name',
        'discount_percentage',
    ];

    protected $casts = [
        'order_id'           => 'integer',
        'store_type'         => 'integer',
        'product_id'         => 'integer',
        'temp_id'            => 'integer',
        'quantity'           => 'integer',
        'price'              => 'decimal:2',
        'price_euro'         => 'decimal:2',
        'discount'           => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
