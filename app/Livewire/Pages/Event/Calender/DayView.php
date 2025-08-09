<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DayView extends Component
{
    public $date;

    #[Computed]
    public function events()
    {
        // Ambil semua acara yang disetujui untuk rentang minggu ini
        return EventRecurrence::with([
            'event.organization:id,name',
            'event.eventCategory:id,name,color',
            'event.locations:id,name',
            'event.customLocations:id,address'
        ])
            ->where('date', $this->date)
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.event.calender.day-view', [
            'eventsByDay' => $this->events
        ]);
    }
}
