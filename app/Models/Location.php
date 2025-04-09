<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'is_active',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    #[Scope]
    public function active($query)
    {
        return $query->where('is_active', 'active');
    }
}
