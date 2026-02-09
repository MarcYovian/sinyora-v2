<?php

namespace App\Livewire\Pages\Event;

use App\Models\EventRecurrence;
use Livewire\Attributes\On;
use Livewire\Component;

class EventDetailsModal extends Component
{
    public ?EventRecurrence $eventRecurrence = null;

    #[On('showEventDetails')]
    public function showDetails($eventId)
    {
        $this->eventRecurrence = EventRecurrence::query()
            ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
            ->with([
                'event' => fn($q) => $q->select([
                    'id', 'name', 'description', 'organization_id', 'event_category_id',
                ]),
                'event.eventCategory' => fn($q) => $q->select(['id', 'name', 'color']),
                'event.organization' => fn($q) => $q->select(['id', 'name']),
                'event.locations' => fn($q) => $q->select(['locations.id', 'locations.name', 'locations.color']),
                'event.customLocations' => fn($q) => $q->select(['custom_locations.id', 'custom_locations.address']),
            ])
            ->find($eventId);

        $this->dispatch('open-modal', 'event-details-modal');
    }

    public function render()
    {
        return view('livewire.pages.event.event-details-modal');
    }
}
