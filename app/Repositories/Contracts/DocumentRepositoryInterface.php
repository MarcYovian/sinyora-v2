<?php

namespace App\Repositories\Contracts;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

interface DocumentRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Document;
    public function create(array $data): Document;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
