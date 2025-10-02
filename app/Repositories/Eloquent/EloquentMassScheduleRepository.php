<?php

namespace App\Repositories\Eloquent;

use App\Models\MassSchedule;
use App\Repositories\Contracts\MassScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentMassScheduleRepository implements MassScheduleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return MassSchedule::orderBy('day_of_week')->orderBy('start_time')->get();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): MassSchedule
    {
        return MassSchedule::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(MassSchedule $massSchedule): bool
    {
        return $massSchedule->delete();
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): ?MassSchedule
    {
        return MassSchedule::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(MassSchedule $massSchedule, array $data): bool
    {
        return $massSchedule->update($data);
    }
}
