<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmaRepair extends Model
{
    use HasFactory;

    protected $table = 'rma_repairs';

    protected $fillable = [
        'ticket_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'device_type',
        'device_type_other',
        'brand',
        'brand_other',
        'model',
        'password_type',
        'password_value',
        'accessories',
        'problem_description',
        'price',
        'signature',
        'status',
        'custom_status',
        'delivered',
        'technician_notes',
    ];

    public $timestamps = false; // Legacy table uses created_at but not updated_at by default
}
