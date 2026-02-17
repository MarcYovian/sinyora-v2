<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentFinalizationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected EventCreationService $eventCreationService,
        protected BorrowingManagementService $borrowingManagementService,
        protected invitationCreationService $invitationCreationService
    ) {}

    public function finalize(array $data)
    {
        $type = data_get($data, 'type');
        $docId = data_get($data, 'id');

        Log::info('Document finalization started.', [
            'document_id' => $docId,
            'type' => $type,
            'events_count' => count(data_get($data, 'events', [])),
        ]);

        DB::transaction(function () use ($data, $type, $docId) {
            $document = $this->updateDocumentAndSignatures($data);

            foreach (data_get($data, 'events') as $event) {
                if ($type === DocumentType::LICENSING->value) {
                    $this->eventCreationService->createEventFromDocument($document, $event, data_get($data, 'document_information'));
                } elseif ($type === DocumentType::BORROWING->value) {
                    $this->borrowingManagementService->createBorrowingFromDocument($document, $event);
                } elseif ($type === DocumentType::INVITATION->value) {
                    $this->invitationCreationService->createInvitationFromDocument($document, $event, data_get($data, 'document_information'));
                }
            }

            Log::info('Document finalization completed.', [
                'document_id' => $docId,
                'type' => $type,
            ]);
        });
    }

    private function updateDocumentAndSignatures(array $data): Document
    {
        $document = $this->documentRepository->findOrFail(data_get($data, 'id'));

        $document->update([
            'email' => data_get($data, 'document_information.emitter_email'),
            'subject' => implode(', ', data_get($data, 'document_information.subjects', [])),
            'city' => data_get($data, 'document_information.document_city'),
            'doc_date' => data_get($data, 'document_information.document_date.date'),
            'doc_num' => data_get($data, 'document_information.document_number'),
            'status' => DocumentStatus::DONE,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        // Hapus tanda tangan lama untuk menghindari duplikasi
        $document->signatures()->delete();
        $document->signatures()->createMany(data_get($data, 'signature_blocks'));

        return $document;
    }
}
