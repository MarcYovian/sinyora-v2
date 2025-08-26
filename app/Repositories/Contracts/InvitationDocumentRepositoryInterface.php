<?php

namespace App\Repositories\Contracts;

use App\Models\InvitationDocument;
use Illuminate\Support\Collection;

interface InvitationDocumentRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): InvitationDocument;
    public function findById(int $id): ?InvitationDocument;
    public function update(int $id, array $data): InvitationDocument;
    public function delete(int $id): bool;
}
