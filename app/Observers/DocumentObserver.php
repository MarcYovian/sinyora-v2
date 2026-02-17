<?php

namespace App\Observers;

use App\Enums\DocumentStatus;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        Log::debug('Document created', [
            'document_id' => $document->id,
            'file_name' => $document->original_file_name,
            'submitter_type' => $document->submitter_type,
            'submitter_id' => $document->submitter_id,
        ]);
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        $changes = $document->getChanges();

        // Log status transition
        if (isset($changes['status'])) {
            $oldStatus = $document->getOriginal('status');
            $newStatus = $document->status;

            Log::debug('Document status changed', [
                'document_id' => $document->id,
                'from' => $oldStatus instanceof DocumentStatus ? $oldStatus->value : $oldStatus,
                'to' => $newStatus instanceof DocumentStatus ? $newStatus->value : $newStatus,
                'processed_by' => $document->processed_by,
            ]);
        }

        // Log analysis result update
        if (isset($changes['analysis_result'])) {
            Log::debug('Document analysis result updated', [
                'document_id' => $document->id,
            ]);
        }
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        Log::debug('Document deleted', [
            'document_id' => $document->id,
            'file_name' => $document->original_file_name,
        ]);
    }
}
