<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensingDocument extends Model
{
    protected $fillable = [
        'description',
        'start_datetime',
        'end_datetime',
    ];

    public function documents()
    {
        $this->morphToMany(Document::class, 'detailable');
    }

    public function events()
    {
        return $this->morphMany(Event::class, 'document_typable');
    }

    public function borrowings()
    {
        return $this->morphMany(Borrowing::class, 'document_typable');
    }
}
