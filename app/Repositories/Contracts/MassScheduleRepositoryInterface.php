<?php

namespace App\Repositories\Contracts;

use App\Models\MassSchedule;
use Illuminate\Database\Eloquent\Collection;

interface MassScheduleRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?MassSchedule;
    public function create(array $data): MassSchedule;
    public function update(MassSchedule $massSchedule, array $data): bool;
    public function delete(MassSchedule $massSchedule): bool;
}
