<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceOnline extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'invoice_online';

    protected $fillable = [
        'order_id',
        'date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
