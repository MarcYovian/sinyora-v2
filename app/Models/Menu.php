<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    protected $fillable = [
        'main_menu',
        'menu',
        'route_name',
        'icon',
        'is_active',
        'sort'
    ];
}
