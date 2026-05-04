<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'products';

    protected $fillable = [
        'supplier_id',
        'category_id',
        'menu_item_id',
        'name',
        'namegr',
        'options',
        'description',
        'descriptiongr',
        'price',
        'price_cy',
        'price_sup_cy',
        'price_cy_unconverted',
        'code',
        'availability',
        'availability_cy',
        'active',
        'color',
        'color_gr',
        'material',
        'material_gr',
        'ledtype',
        'tasi',
        'lumens',
        'lifetime',
        'usage_en',
        'usage_gr',
        'lightangle',
        'dimensions',
        'basetype',
        'basetype_gr',
        'cover',
        'cover_gr',
        'supply',
        'supply_gr',
        'tasi_exodou',
        'output',
        'output_type',
        'temp_use',
        'profit',
        'sintelestis_isxios',
        'warranty',
        'certificate',
        'offer',
        'new_arrival',
        'apodosi_xromatos',
        'skey',
        'weight',
        'size',
        'minquantity',
        'store_profit',
        'wholesaler_profit',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'price_cy'           => 'decimal:2',
        'price_sup_cy'       => 'decimal:2',
        'price_cy_unconverted'=> 'decimal:2',
        'profit'             => 'decimal:2',
        'store_profit'       => 'decimal:2',
        'wholesaler_profit'  => 'decimal:2',
        'availability'       => 'integer',
        'availability_cy'    => 'integer',
        'minquantity'        => 'integer',
        'weihgt'             => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(AdminUser::class, 'supplier_id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    // Scope for active products (DB stores 'yes'/'no' strings)
    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    // Scope for new arrivals
    public function scopeNewArrivals($query)
    {
        return $query->where('new_arrival', 'yes')->where('active', 'yes');
    }

    // Scope for offers
    public function scopeOffers($query)
    {
        return $query->where('offer', 'yes')->where('active', 'yes');
    }

    // Scope for available products
    public function scopeAvailable($query)
    {
        return $query->where('availability', '>', 0)
                     ->where('availability_cy', '>', 0);
    }
}
