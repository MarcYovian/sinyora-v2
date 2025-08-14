<?php

namespace App\Repositories\Eloquent;

use App\Enums\EventApprovalStatus;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Repositories\Contracts\EventRecurrenceRepositoryInterface;
use Illuminate\Support\Collection;
// use App\Models\EventRecurrenceRepository;

class EloquentEventRecurrenceRepository implements EventRecurrenceRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return EventRecurrence::all();
    }

    /**
     * @inheritDoc
     */
    public function create(Event $event, array $data): Collection
    {
        return $event->eventRecurrences()->createMany($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $eventRecurrence = $this->findById($id);
        if ($eventRecurrence) {
            return $eventRecurrence->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findByEventId(int $eventId): EventRecurrence|null
    {
        return EventRecurrence::where('event_id', $eventId)->first();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): bool
    {
        $eventRecurrence = $this->findById($id);
        if ($eventRecurrence) {
            return $eventRecurrence->update($data);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): EventRecurrence|null
    {
        return EventRecurrence::find($id);
    }

    public function findConflicts(array $potentialRecurrences, array $locations): Collection
    {
        if (empty($potentialRecurrences)) {
            return new Collection();
        }

        $conflicts = EventRecurrence::query()->whereHas('event.locations', function ($q) use ($locations) {
            $q->whereIn('locations.id', $locations);
        })->whereHas('event', function ($q) {
            $q->where('status', EventApprovalStatus::APPROVED);
        })->where(function ($query) use ($potentialRecurrences) {
            foreach ($potentialRecurrences as $recurrence) {
                $query->orWhere(function ($subQuery) use ($recurrence) {
                    $subQuery->where('date', $recurrence['date'])
                        ->where('time_start', '<', $recurrence['time_end'])
                        ->where('time_end', '>', $recurrence['time_start']);
                });
            }
        });

        return $conflicts->get();
    }
}
