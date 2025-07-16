<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'duration',
        'describable_type',
        'describable_id',
    ];

    public function describable()
    {
        return $this->morphTo();
    }
}
