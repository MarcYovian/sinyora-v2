<?php

namespace App\Repositories\Eloquent;

use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentLocationRepository implements LocationRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Location::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Location
    {
        return Location::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $location = $this->findById($id);

        return $location->delete();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Location
    {
        return Location::find($id);
    }

    /**
     * @inheritDoc
     */
    public function getActiveLocations(): Collection
    {
        return Location::active()->get();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Location
    {
        $location = $this->findById($id);
        if ($location) {
            $location->update($data);
            return $location;
        }
        throw new \Exception("Location with ID {$id} not found.");
    }

    public function getAllOrderedByName(): Collection
    {
        return Location::orderBy('name')->get();
    }
}
