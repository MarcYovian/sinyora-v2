<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Calender extends Component
{
    #[Url(history: true)]
    public $year;

    #[Url(history: true)]
    public $month;

    #[Url(history: true)]
    public $day;

    #[Url(as: 'tampilan', history: true)]
    public $viewMode = 'month'; // 'month', 'week', 'day'

    public function mount()
    {
        // Inisialisasi tanggal jika URL kosong
        $this->year = $this->year ?? now()->year;
        $this->month = $this->month ?? now()->month;
        $this->day = $this->day ?? now()->day;
    }

    // Menggunakan Computed Property untuk efisiensi
    #[Computed]
    public function currentDate()
    {
        return Carbon::create($this->year, $this->month, $this->day);
    }

    #[Computed]
    public function events()
    {
        $startDate = $this->currentDate->clone()->startOf($this->viewMode === 'month' ? 'month' : 'week')->startOfWeek();
        $endDate = $this->currentDate->clone()->endOf($this->viewMode === 'month' ? 'month' : 'week')->endOfWeek();

        // Ambil semua acara yang sudah disetujui untuk rentang waktu yang relevan
        return EventRecurrence::with(['event:id,name,status'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    // --- Navigasi ---
    public function previous()
    {
        $newDate = $this->currentDate->sub(1, $this->viewMode);
        $this->updateDate($newDate);
    }

    public function next()
    {
        $newDate = $this->currentDate->add(1, $this->viewMode);
        $this->updateDate($newDate);
    }

    public function goToToday()
    {
        $this->updateDate(now());
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    private function updateDate(Carbon $date)
    {
        $this->year = $date->year;
        $this->month = $date->month;
        $this->day = $date->day;
    }

    public function placeholder()
    {
        return view('livewire.loader.calender-loader');
    }

    public function render()
    {
        return view('livewire.pages.event.calender');
    }
}
