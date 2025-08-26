<?php

namespace App\Repositories\Eloquent;

use App\Models\BorrowingDocument;
use App\Repositories\Contracts\BorrowingDocumentRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentBorrowingDocumentRepository implements BorrowingDocumentRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection {
        return BorrowingDocument::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): BorrowingDocument {
        return BorrowingDocument::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool {
        $document = $this->findById($id);
        if ($document) {
            return $document->delete();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?BorrowingDocument {
        return BorrowingDocument::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): BorrowingDocument {
        $document = $this->findById($id);
        if ($document) {
            $document->update($data);
            return $document;
        }

        throw new \Exception('Document not found');
    }
}
