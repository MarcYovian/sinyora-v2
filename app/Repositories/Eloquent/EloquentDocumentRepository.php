<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\StoreDocumentData;
use App\DataTransferObjects\UpdateDocumentAnalysisData;
use App\Models\Document;
use App\Models\GuestSubmitter;
use App\Models\User;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

// use App\Models\DocumentRepository;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Document::latest()->get();
    }

    /**
     * @inheritDoc
     */
    public function create(User|GuestSubmitter $submitter, StoreDocumentData $data): Document
    {
        return $submitter->documents()->create([
            'document_path' => $data->document_path,
            'original_file_name' => $data->original_file_name,
            'mime_type' => $data->mime_type,
            'status' => $data->status
        ]);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $document = $this->findById($id);
        if ($document) {
            return $document->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Document
    {
        return Document::find($id);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): Document
    {
        return Document::findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): bool
    {
        $document = $this->findById($id);
        if ($document) {
            return $document->update($data);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function updateAnalysisResult(int $id, UpdateDocumentAnalysisData $data): Document
    {
        $document = $this->findOrFail($id);

        // 2. Memperbarui atribut dan menyimpan
        $document->fill($data->toArray());
        $document->save();

        // 3. Mengembalikan model yang sudah diperbarui
        return $document;
    }
}
