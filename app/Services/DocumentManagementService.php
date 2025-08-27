<?php

namespace App\Services;

use App\DataTransferObjects\StoreDocumentData;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Events\DocumentProposalCreated;
use App\Models\Document;
use App\Models\GuestSubmitter;
use App\Models\User;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function storeNewDocument(UploadedFile $file, User|GuestSubmitter $submitter)
    {
        $document = DB::transaction(function () use ($file, $submitter) {
            $path = $file->store('documents/proposals', 'public');
            $mimeType = Storage::disk('public')->mimeType($path);

            $documentData = new StoreDocumentData(
                document_path: $path,
                original_file_name: $file->getClientOriginalName(),
                mime_type: $mimeType,
                status: DocumentStatus::PENDING
            );

            return $this->documentRepository->create($submitter, $documentData);
        });

        if ($document && $submitter instanceof GuestSubmitter) {
            DocumentProposalCreated::dispatch($submitter, $document);
        }

        if ($document && $submitter instanceof User) {
            // DocumentProposalCreated::dispatch($submitter, $document);
        }

        return $document;
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


    public function deleteDocument(Document $document)
    {
        return DB::transaction(function () use ($document) {
            Storage::disk('public')->delete($document->document_path);
            return $this->documentRepository->delete($document->id);
        });
    }
}
