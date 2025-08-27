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

    protected $casts = [
        'date' => 'date:Y-m-d',
        'time_start' => 'date:H:i:s',
        'time_end' => 'date:H:i:s',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
