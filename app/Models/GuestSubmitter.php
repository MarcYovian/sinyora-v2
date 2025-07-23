<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestSubmitter extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone_number',
    ];

    /**
     * Get all of the events that are assigned to this guest submitter.
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'creator');
    }

    public function borrowings()
    {
        return $this->morphMany(Borrowing::class, 'creator');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'submitter');
    }
}
