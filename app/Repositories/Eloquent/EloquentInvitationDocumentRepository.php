<?php

namespace App\Repositories\Eloquent;

use App\Models\InvitationDocument;
use App\Repositories\Contracts\InvitationDocumentRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentInvitationDocumentRepository implements InvitationDocumentRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return InvitationDocument::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): InvitationDocument
    {
        return InvitationDocument::create($data);
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
    public function findById(int $id): ?InvitationDocument
    {
        return InvitationDocument::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): InvitationDocument
    {
        $document = $this->findById($id);
        if ($document) {
            $document->update($data);
            return $document;
        }

        throw new \Exception('Document not found');
    }
}
