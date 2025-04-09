<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
