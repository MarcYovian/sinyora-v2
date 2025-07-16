<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_path',
        'original_file_name',
        'mime_type',
        'analysis_result',
        'email',
        'phone',
        'subject',
        'city',
        'doc_date',
        'doc_num',
        'status',
        'processed_by',
        'processed_at'
    ];

    public function submitter()
    {
        return $this->morphTo();
    }

    public function licensingDocuments()
    {
        return $this->morphedByMany(LicensingDocument::class, 'detailable');
    }

    public function invitationDocuments()
    {
        return $this->morphedByMany(InvitationDocument::class, 'detailable');
    }

    public function borrowingDocuments()
    {
        return $this->morphedByMany(BorrowingDocument::class, 'detailable');
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
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
