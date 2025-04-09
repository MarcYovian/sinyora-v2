<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'color',
        'is_active',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    #[Scope]
    public function active($query)
    {
        return $query->where('is_active', true);
    }
}
