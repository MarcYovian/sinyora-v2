<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WeekView extends Component
{
    public $startOfWeek;

    #[Computed]
    public function events()
    {
        // Tentukan tanggal akhir minggu untuk query
        $endOfWeek = $this->startOfWeek->clone()->endOfWeek();

        // Ambil semua acara yang disetujui untuk rentang minggu ini
        return EventRecurrence::with([
            'event.organization:id,name',
            'event.eventCategory:id,name,color',
            'event.locations:id,name',
            'event.customLocations:id,address'
        ])
            ->whereBetween('date', [$this->startOfWeek, $endOfWeek])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->get();
    }
    public function render()
    {
        $eventsByDay = $this->events->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });
        return view('livewire.pages.event.calender.week-view', [
            'eventsByDay' => $eventsByDay
        ]);
    }
}
