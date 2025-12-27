<?php

namespace App\Repositories\Eloquent;

use App\Enums\EventApprovalStatus;
use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Collection;
// use App\Models\EventRepository;

class EloquentEventRepository implements EventRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Event::all();
    }

    /**
     * @inheritDoc
     */
    public function create(User|GuestSubmitter $creator, Event $data): Event
    {
        return $creator->events()->save($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $event = $this->findById($id);
        if ($event) {
            return $event->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Event|null
    {
        return Event::find($id);
    }

    /**
     * @inheritDoc
     */
    public function findByOrganizationId(int $organizationId): Collection
    {
        return Event::where('organization_id', $organizationId)->get();
    }

    /**
     * @inheritDoc
     */
    public function isScheduleAvailable(array $recurrences, array $locations): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): bool
    {
        $event = $this->findById($id);
        if ($event) {
            return $event->update($data);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function changeStatus(Event $event, EventApprovalStatus $status, ?string $rejectionReason = null): bool
    {
        $event->status = $status;
        if ($status === EventApprovalStatus::REJECTED && $rejectionReason !== null) {
            $event->rejection_reason = $rejectionReason;
        }
        return $event->save();
    }

    public function getMassEvents(): Collection
    {
        return Event::with(['eventRecurrences:event_id,date,time_start', 'eventCategory:id,name'])
            ->whereHas('eventCategory', function ($query) {
                $query->where('is_active', true)->where('is_mass_category', true);
            })
            ->get();
    }
}
