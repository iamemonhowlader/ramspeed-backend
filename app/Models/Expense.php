<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';
    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'Company_reg_num',
        'invoice',
        'other',
        'date',
        'Exp_Date',
        'GROSS',
        'VAT',
        'Calculated_VAT',
        'Calculated_NET',
        'cancelled',
        'type',
        'Service_Receipt',
        'ZeroVAT'
    ];

    protected $casts = [
        'date' => 'date',
        'Exp_Date' => 'date',
        'GROSS' => 'decimal:2',
        'VAT' => 'decimal:2',
        'Calculated_VAT' => 'decimal:2',
        'Calculated_NET' => 'decimal:2',
        'ZeroVAT' => 'decimal:2',
        'cancelled' => 'integer',
        'Service_Receipt' => 'integer'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'user_id');
    }
}
