<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Livewire\Component;

class UpcomingEvents extends Component
{
    public $events;

    public function mount()
    {
        $this->events = EventRecurrence::with(['event', 'event.organization', 'event.eventCategory', 'event.locations'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->where('date', '>=', now())
            ->orderBy('date')
            ->take(6)
            ->get(['id', 'event_id', 'time_start', 'time_end', 'date'])->sortBy('date');
    }
    public function render()
    {
        return view('livewire.pages.event.upcoming-events');
    }
}
