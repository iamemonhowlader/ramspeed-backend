<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'suppliers_info';

    protected $fillable = [
        'username',
        'user_id',
        'full_name',
        'cperson',
        'email',
        'address',
        'post_code',
        'city',
        'country',
        'profit',
        'cyprofit',
        'cysupprofit',
        'cytax',
        'phone',
        'fax',
        'vat_num',
        'company_reg_num',
        'website',
    ];

    protected $casts = [
        'profit'     => 'decimal:2',
        'cyprofit'   => 'decimal:2',
        'cysupprofit'=> 'decimal:2',
        'cytax'      => 'decimal:2',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id');
    }

    public function discount()
    {
        return $this->hasOne(SupplierDiscount::class, 'supplier_id');
    }
}
