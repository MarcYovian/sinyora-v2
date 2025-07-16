<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomLocation extends Model
{
    protected $fillable = [
        'address',
    ];

    public function events()
    {
        return $this->morphedByMany(Event::class, 'locatable', 'event_locatables');
    }
}
