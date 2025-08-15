<?php

namespace App\Repositories\Contracts;

use App\Models\Activity;
use Illuminate\Support\Collection;

interface ActivityRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Activity;
    public function create(array $data): Activity;
    public function update(int $id, array $data): Activity;
    public function delete(int $id): bool;
}
