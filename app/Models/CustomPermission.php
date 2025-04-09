<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;

class CustomPermission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group',
        'route_name',
        'default',
    ];

    public function groupPermission()
    {
        return $this->belongsTo(Group::class, 'group', 'id');
    }

    public function scopeDefault($query)
    {
        return $query->where('default', '=', 'Default');
    }
}
