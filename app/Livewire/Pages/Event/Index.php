<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Index extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Event')]

    public $events = [];
    public function mount()
    {
        $this->events = EventRecurrence::with(['event:id,name,organization_id,status,event_category_id', 'event.organization:id,code', 'event.eventCategory:id,color'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })->get(['id', 'event_id', 'time_start', 'time_end', 'date'])
            ->map(function ($recurrence) {
                return [
                    'title' => $recurrence->event->name . ' - ' . $recurrence->event->organization->code,
                    'start' => $recurrence->date->format('Y-m-d') . 'T' . $recurrence->time_start->format('H:i:s'),
                    'end' => $recurrence->date->format('Y-m-d') . 'T' . $recurrence->time_end->format('H:i:s'),
                    'color' => $recurrence->event->eventCategory->color
                ];
            });
    }

    public function render()
    {
        return view('livewire.pages.event.index');
    }
}
