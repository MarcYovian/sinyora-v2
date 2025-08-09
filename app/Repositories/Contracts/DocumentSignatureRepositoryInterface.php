<?php

namespace App\Repositories\Contracts;

use App\Models\Signature;
use Illuminate\Database\Eloquent\Collection;

interface DocumentSignatureRepositoryInterface
{
    public function findByDocumentId(int $documentId): Collection;

    public function findById(int $id): ?Signature;

    public function create(array $data): Signature;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
