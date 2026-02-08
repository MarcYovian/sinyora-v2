<?php

namespace App\Models;

use App\Observers\CustomPermissionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;

#[ObservedBy(CustomPermissionObserver::class)]
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
