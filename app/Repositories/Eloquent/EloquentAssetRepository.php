<?php

namespace App\Repositories\Eloquent;

use App\Models\Asset;
use App\Repositories\Contracts\AssetRepositoryInterface;
use Illuminate\Support\Collection;
// use App\Models\AssetRepository;

class EloquentAssetRepository implements AssetRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Asset::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $asset = $this->findOrFail($id);

        return $asset->delete();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Asset
    {
        return Asset::find($id);
    }

    /**
     * @inheritDoc
     */
    public function getActiveAssets(): Collection
    {
        return Asset::active()->get();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Asset
    {
        $asset = $this->findOrFail($id);
        if ($asset) {
            $asset->update($data);
            return $asset;
        }
        throw new \Exception("Asset with ID {$id} not found.");
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $id): Asset
    {
        return Asset::findOrFail($id);
    }

    public function getAllOrderedByName(): Collection
    {
        return Asset::orderBy('name')->get();
    }

    public function getAvailableAssetsBetween(string $startTime, string $endTime): Collection
    {
        $assets = Asset::query()
            ->active()
            ->withBorrowedQuantityBetween(startTime: $startTime, endTime: $endTime)
            ->get();

        return $assets->map(function ($asset) {
            $asset->borrowed_quantity = $asset->borrowings->sum(fn($b) => $b->pivot->quantity ?? 0);
            $asset->available_stock = $asset->quantity - $asset->borrowed_quantity;
            return $asset;
        });
    }
}
