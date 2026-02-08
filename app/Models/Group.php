<?php

namespace App\Models;

use App\Observers\GroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(GroupObserver::class)]
class Group extends Model
{
    protected $fillable = [
        'name',
    ];

    public function permissions()
    {
        return $this->hasMany(CustomPermission::class, 'group', 'id');
    }
}

