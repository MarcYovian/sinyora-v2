<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Livewire\Traits\CalendarLayoutTrait;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DayView extends Component
{
    use CalendarLayoutTrait;
    public $date;

    #[Computed]
    public function events()
    {
        // Ambil semua acara yang disetujui untuk rentang minggu ini
        $eventRecurrences = EventRecurrence::with([
            'event.organization:id,name',
            'event.eventCategory:id,name,color',
            'event.locations:id,name,color',
            'event.customLocations:id,address'
        ])
            ->where('date', $this->date)
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->get();

        $eventRecurrences->each(function ($recurrence) {
            if ($recurrence->event) {
                // Ambil semua warna dari lokasi yang terkait
                $locationColors = $recurrence->event->locations->pluck('color')->filter()->all();

                // Hitung warna background dan tambahkan sebagai properti baru
                $recurrence->event->computed_background_color = BlendColorService::blend($locationColors);
            }
        });

        return $eventRecurrences;
    }

    public function render()
    {
        $events = $this->events;
        $this->calculateLayoutProperties($events);
        return view('livewire.pages.event.calender.day-view', [
            'eventsByDay' => $this->events
        ]);
    }
}
