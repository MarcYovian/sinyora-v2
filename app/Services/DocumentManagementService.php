<?php

namespace App\Services;

use App\DataTransferObjects\StoreDocumentData;
use App\Enums\DocumentStatus;
use App\Events\DocumentProposalCreated;
use App\Models\Document;
use App\Models\GuestSubmitter;
use App\Models\User;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentManagementService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository
    ) {}

    /**
     * Store a new document submitted by a user or guest.
     */
    public function storeNewDocument(UploadedFile $file, User|GuestSubmitter $submitter): Document
    {
        Log::info('Storing new document', [
            'file_name' => $file->getClientOriginalName(),
            'submitter_type' => class_basename($submitter),
            'submitter_id' => $submitter->id ?? null,
            'user_id' => Auth::id(),
        ]);

        return DB::transaction(function () use ($file, $submitter) {
            try {
                $path = $file->store('documents/proposals', 'public');
                $mimeType = Storage::disk('public')->mimeType($path);

                // Sanitize user-supplied filename
                $originalName = Str::limit(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    200
                ) . '.' . $file->getClientOriginalExtension();

                $documentData = new StoreDocumentData(
                    document_path: $path,
                    original_file_name: $originalName,
                    mime_type: $mimeType,
                    status: DocumentStatus::PENDING
                );

                $document = $this->documentRepository->create($submitter, $documentData);

                if ($document && $submitter instanceof GuestSubmitter) {
                    DocumentProposalCreated::dispatch($submitter, $document);
                }

                Log::info('Document stored successfully', [
                    'document_id' => $document?->id,
                    'file_name' => $file->getClientOriginalName(),
                    'submitter_type' => class_basename($submitter),
                    'submitter_id' => $submitter->id ?? null,
                    'user_id' => Auth::id(),
                ]);

                return $document;
            } catch (\Throwable $th) {
                Log::error('Failed to store document', [
                    'file_name' => $file->getClientOriginalName(),
                    'submitter_type' => class_basename($submitter),
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);

                throw $th;
            }
        });
    }

    /**
     * Delete a document and its associated file from storage.
     */
    public function deleteDocument(Document $document): bool
    {
        Log::info('Deleting document', [
            'document_id' => $document->id,
            'file_name' => $document->original_file_name,
            'user_id' => Auth::id(),
        ]);

        return DB::transaction(function () use ($document) {
            try {
                $filePath = $document->document_path;

                $deleted = Storage::disk('public')->delete($filePath);
                if (!$deleted) {
                    Log::warning('File not found on disk during document deletion', [
                        'document_id' => $document->id,
                        'path' => $filePath,
                    ]);
                }

                $result = $this->documentRepository->delete($document->id);

                Log::info('Document deleted successfully', [
                    'document_id' => $document->id,
                    'file_deleted' => $deleted,
                    'user_id' => Auth::id(),
                ]);

                return $result;
            } catch (\Throwable $th) {
                Log::error('Failed to delete document', [
                    'document_id' => $document->id,
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);

                throw $th;
            }
        });
    }
}
