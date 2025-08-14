<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MonthView extends Component
{
    public $year;
    public $month;

    #[Computed]
    public function currentDate()
    {
        return Carbon::create($this->year, $this->month, 1);
    }

    #[Computed]
    public function events()
    {
        $startDate = $this->currentDate->clone()->startOf('month')->startOfWeek();
        $endDate = $this->currentDate->clone()->endOf('month')->endOfWeek();

        // Ambil semua acara yang sudah disetujui untuk rentang waktu yang relevan
        $eventRecurrences = EventRecurrence::with(['event.eventCategory:id,name,color', 'event.locations:id,name,color'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'event_id', 'date', 'time_start')
            ->orderBy('time_start')
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
        $eventByDay = $this->events->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        return view('livewire.pages.event.calender.month-view', [
            'eventsByDay' => $eventByDay
        ]);
    }
}
