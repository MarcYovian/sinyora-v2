<?php

namespace App\Models;

use App\DataTransferObjects\LetterData;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Document extends Model
{
    protected $fillable = [
        'submitter_type',
        'submitter_id',
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => DocumentStatus::class,
        'doc_date' => 'date',
        'processed_at' => 'datetime',
        'analysis_result' => LetterData::class,
    ];

    public function submitter(): MorphTo
    {
        return $this->morphTo();
    }

    public function licensingDocuments(): MorphToMany
    {
        return $this->morphedByMany(LicensingDocument::class, 'detailable');
    }

    public function invitationDocuments(): MorphToMany
    {
        return $this->morphedByMany(InvitationDocument::class, 'detailable');
    }

    public function borrowingDocuments(): MorphToMany
    {
        return $this->morphedByMany(BorrowingDocument::class, 'detailable');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    #[Scope]
    public function pending(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::PENDING);
    }

    #[Scope]
    public function processed(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::PROCESSED);
    }

    #[Scope]
    public function done(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::DONE);
    }
}
