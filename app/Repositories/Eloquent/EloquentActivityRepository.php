<?php

namespace App\Repositories\Eloquent;

use App\Models\Activity;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Support\Collection;
// use App\Models\ActivityRepository;

class EloquentActivityRepository implements ActivityRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Activity::all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Activity
    {
        return Activity::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $activity = $this->findById($id);
        if ($activity) {
            return $activity->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Activity
    {
        return Activity::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Activity
    {
        $activity = $this->findById($id);
        if ($activity) {
            $activity->update($data);
            return $activity;
        }
        throw new \Exception("Activity with ID {$id} not found.");
    }
}
