<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'namegr',
        'parent',
        'active_page',
        'type',
        'sort',
        'preview',
        'icon',
        'custom_link',
        'link_target',
        'show_in_menu',
    ];
}
