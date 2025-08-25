<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\StoreDocumentData;
use App\DataTransferObjects\UpdateDocumentAnalysisData;
use App\Models\Document;
use App\Models\GuestSubmitter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DocumentRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Document;
    public function findOrFail(int $id): Document;
    public function create(User|GuestSubmitter $submitter, StoreDocumentData $data): Document;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function updateAnalysisResult(int $id, UpdateDocumentAnalysisData $data): Document;
}
