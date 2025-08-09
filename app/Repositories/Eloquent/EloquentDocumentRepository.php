<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
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
    public function create(array $data): Document
    {
        return Document::create($data);
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
    public function update(int $id, array $data): bool
    {
        $document = $this->findById($id);
        if ($document) {
            return $document->update($data);
        }
        return false;
    }
}
