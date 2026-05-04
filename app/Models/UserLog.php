<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_logs';

    protected $fillable = [
        'username',
        'date',
        'action'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'username', 'username');
    }
}
