<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_name',
        'document_path',
        'original_file_name',
        'mime_type',
        'status',
        'processed_by',
        'processed_at'
    ];

    public function submitter()
    {
        return $this->morphTo();
    }
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
