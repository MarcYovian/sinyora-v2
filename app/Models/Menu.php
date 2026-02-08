<?php

namespace App\Models;

use App\Observers\MenuObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(MenuObserver::class)]
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

