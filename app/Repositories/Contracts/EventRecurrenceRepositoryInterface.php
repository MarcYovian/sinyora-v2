<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use App\Models\EventRecurrence;
use Illuminate\Support\Collection;

interface EventRecurrenceRepositoryInterface
{
    public function all(): Collection;
    public function findByEventId(int $eventId): ?EventRecurrence;
    public function findById(int $id): ?EventRecurrence;
    public function create(Event $event, array $data): Collection;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function findConflicts(array $recurrences, array $locations): Collection;
}
