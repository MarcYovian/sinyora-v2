<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRecurrence extends Model
{
    protected $fillable = [
        'event_id',
        'date',
        'time_start',
        'time_end',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
