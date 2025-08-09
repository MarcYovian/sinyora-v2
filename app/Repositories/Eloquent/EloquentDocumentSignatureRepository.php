<?php

namespace App\Repositories\Eloquent;

use App\Models\Signature;
use App\Repositories\Contracts\DocumentSignatureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
// use App\Models\DocumentSignatureRepository;

class EloquentDocumentSignatureRepository implements DocumentSignatureRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(array $data): Signature
    {
        return Signature::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $signature = $this->findById($id);
        if ($signature) {
            return $signature->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findByDocumentId(int $documentId): Collection
    {
        return Signature::where('document_id', $documentId)->get();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): bool
    {
        $signature = $this->findById($id);
        if ($signature) {
            return $signature->update($data);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Signature|null
    {
        return Signature::find($id);
    }
}
