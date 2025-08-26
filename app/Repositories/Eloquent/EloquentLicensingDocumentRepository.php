<?php

namespace App\Repositories\Eloquent;

use App\Models\LicensingDocument;
use App\Repositories\Contracts\LicensingDocumentRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentLicensingDocumentRepository implements LicensingDocumentRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return LicensingDocument::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): LicensingDocument
    {
        return LicensingDocument::create($data);
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
    public function findById(int $id): ?LicensingDocument
    {
        return LicensingDocument::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): LicensingDocument
    {
        $document = $this->findById($id);
        if ($document) {
            $document->update($data);
            return $document;
        }

        throw new \Exception('Document not found');
    }
}
