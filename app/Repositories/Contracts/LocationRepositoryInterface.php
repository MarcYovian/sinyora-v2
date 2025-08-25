<?php

namespace App\Repositories\Contracts;

use App\Models\Location;
use Illuminate\Support\Collection;

interface LocationRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): Location;
    public function create(array $data): Location;
    public function update(int $id, array $data): Location;
    public function delete(int $id): bool;
    public function getActiveLocations(): Collection;
    public function getAllOrderedByName(): Collection;
}
