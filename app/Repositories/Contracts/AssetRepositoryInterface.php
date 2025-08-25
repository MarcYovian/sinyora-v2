<?php

namespace App\Repositories\Contracts;

use App\Models\Asset;
use Illuminate\Support\Collection;

interface AssetRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): Asset;
    public function findOrFail(int $id): Asset;
    public function create(array $data): Asset;
    public function update(int $id, array $data): Asset;
    public function delete(int $id): bool;
    public function getActiveAssets(): Collection;
    public function getAllOrderedByName(): Collection;
    public function getAvailableAssetsBetween(string $startTime, string $endTime): Collection;
}
