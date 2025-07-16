<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationDocument extends Model
{
    protected $fillable = [
        'event',
        'start_datetime',
        'end_datetime',
        'location',
        'description',
    ];

    public function documents()
    {
        return $this->morphToMany(Document::class, 'detailable');
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }

    public function schedules()
    {
        return $this->morphMany(Schedules::class, 'describable');
    }
}
