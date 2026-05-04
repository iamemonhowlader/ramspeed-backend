<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'orders_temp';

    protected $fillable = [
        'member_id',
        'client_id',
        'full_name',
        'email',
        'phone',
        'address',
        'post_code',
        'city',
        'country',
        'akis_branch',
        'shipping_type',
        'shipping_cost',
        'subtotal',
        'subtotal_euro',
        'grand_total',
        'grand_total_euro',
        'discount',
        'total_line_discount',
        'total_after_discount',
        'vat',
        'vat_percentage',
        'discount_percentage_to_amount',
        'other_1',
        'other_2',
        'other_3',
        'boxnow_locker_id',
        'boxnow_locker_name',
        'boxnow_locker_address',
        'boxnow_voucher_status',
        'date',
        'status',
        'payment_type',
        'store_payment_type',
        'credit_payment',
        'delivered',
        'cancelled',
        'ZeroVAT',
        'discount_type',
        'code_version',
        'VivaWallet',
    ];

    protected $casts = [
        'date'                         => 'datetime',
        'shipping_cost'                => 'decimal:2',
        'subtotal'                     => 'decimal:2',
        'subtotal_euro'                => 'decimal:2',
        'grand_total'                  => 'decimal:2',
        'grand_total_euro'             => 'decimal:2',
        'discount'                     => 'decimal:2',
        'total_line_discount'          => 'decimal:2',
        'total_after_discount'         => 'decimal:2',
        'vat'                          => 'decimal:2',
        'discount_percentage_to_amount'=> 'decimal:2',
        'vat_percentage'               => 'integer',
        'ZeroVAT'                      => 'integer',
        'discount_type'                => 'integer',
        'code_version'                 => 'integer',
        'VivaWallet'                   => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function invoiceStore()
    {
        return $this->hasOne(InvoiceStore::class, 'order_id');
    }

    public function invoiceOnline()
    {
        return $this->hasOne(InvoiceOnline::class, 'order_id');
    }

    public function invoiceWire()
    {
        return $this->hasOne(InvoiceWire::class, 'order_id');
    }

    public function invoiceWholesale()
    {
        return $this->hasOne(InvoiceWholesale::class, 'order_id');
    }

    // Scope: completed orders
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope: pending bank transfer orders
    public function scopePendingBankTransfer($query)
    {
        return $query->where('status', 'pending')
                     ->where('payment_type', 'Bank transfer');
    }

    // Scope: active (completed or pending bank transfer)
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'completed')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'pending')
                     ->where('payment_type', 'Bank transfer');
              });
        });
    }
}
