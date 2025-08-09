<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
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
        return EventRecurrence::with(['event.eventCategory:id,name,color'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'event_id', 'date', 'time_start')
            ->orderBy('time_start')
            ->get();
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
