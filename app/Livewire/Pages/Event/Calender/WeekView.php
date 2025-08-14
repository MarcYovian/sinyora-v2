<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Livewire\Traits\CalendarLayoutTrait;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WeekView extends Component
{
    use CalendarLayoutTrait;
    public $startOfWeek;

    #[Computed]
    public function events()
    {
        // Tentukan tanggal akhir minggu untuk query
        $endOfWeek = $this->startOfWeek->clone()->endOfWeek();

        // Ambil semua acara yang disetujui untuk rentang minggu ini
        $eventRecurrences = EventRecurrence::with([
            'event.organization:id,name',
            'event.eventCategory:id,name,color',
            'event.locations:id,name,color',
            'event.customLocations:id,address'
        ])
            ->whereBetween('date', [$this->startOfWeek, $endOfWeek])
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
        $eventsByDay = $this->events->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        foreach ($eventsByDay as $day => $dayEvents) {
            $this->calculateLayoutProperties($dayEvents);
        }

        return view('livewire.pages.event.calender.week-view', [
            'eventsByDay' => $eventsByDay
        ]);
    }
}
