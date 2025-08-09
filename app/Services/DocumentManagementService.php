<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentManagementService
{
    protected $documentRepository;
    /**
     * Create a new class instance.
     */
    public function __construct(
        DocumentRepositoryInterface $documentRepository
    ) {
        $this->documentRepository = $documentRepository;
    }

    public function updateDocumentWithAnalysis(array $data)
    {
        $docId = data_get($data, 'id');
        $docData = [
            'email' => data_get($data, 'document_information.emitter_email'),
            'subject' => implode(', ', data_get($data, 'document_information.subjects', [])),
            'city' => data_get($data, 'document_information.document_city'),
            'doc_date' => data_get($data, 'document_information.document_date.date'),
            'doc_num' => data_get($data, 'document_information.document_number'),
            'status' => DocumentStatus::DONE,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ];
        $docType = data_get($data, 'type');
        $signatureBlocks = data_get($data, 'signature_blocks', []);

        DB::transaction(function () use ($docId, $docData, $docType, $signatureBlocks) {
            try {
                $documentUpdated = $this->documentRepository->update($docId, $docData);

                if (!$documentUpdated) {
                    throw new \Exception("Document with ID $docId could not be updated.");
                }

                $document = $this->documentRepository->findById($docId);

                $document->signatures()->createMany($signatureBlocks);

                if ($docType === DocumentType::BORROWING) {
                } elseif ($docType === DocumentType::LICENSING) {
                } elseif ($docType === DocumentType::INVITATION) {
                } else {
                    throw new \Exception("Unknown document type: $docType");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }
}
