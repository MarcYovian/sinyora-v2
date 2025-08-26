<?php

namespace App\Repositories\Contracts;

use App\Models\BorrowingDocument;
use Illuminate\Support\Collection;

interface BorrowingDocumentRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): BorrowingDocument;
    public function findById(int $id): ?BorrowingDocument;
    public function update(int $id, array $data): BorrowingDocument;
    public function delete(int $id): bool;
}
