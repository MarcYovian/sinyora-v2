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
        $this->eventRecurrence = EventRecurrence::with('event.eventCategory', 'event.organization', 'event.locations', 'event.customLocations')->find($eventId);

        $this->dispatch('open-modal', 'event-details-modal');
    }

    public function render()
    {
        return view('livewire.pages.event.event-details-modal');
    }
}
