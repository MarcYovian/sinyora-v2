<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'name',
        'position',
        'document_id',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
