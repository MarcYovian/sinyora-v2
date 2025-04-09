<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class PublicMenu extends Model
{
    protected $table = 'public_menus';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'main_menu',
        'menu',
        'link',
        'link_type',
        'link_anchor',
        'open_in_new_tab',
        'icon',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function uniqueIds()
    {
        return ['id', 'menu'];
    }

    #[Scope]
    public function active($query)
    {
        return $query->where('is_active', true);
    }

    #[Scope]

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort', 'asc');
    }
}
