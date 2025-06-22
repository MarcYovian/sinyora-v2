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
}
