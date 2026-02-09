<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Calender extends Component
{
    /**
     * Cache TTL in seconds (3 minutes for calendar data).
     */
    private const CACHE_TTL = 180;

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

    /**
     * Get the current date as Carbon instance.
     */
    #[Computed]
    public function currentDate()
    {
        return Carbon::create($this->year, $this->month, $this->day);
    }

    /**
     * Get events with caching and optimized eager loading.
     */
    #[Computed]
    public function events()
    {
        $startDate = $this->currentDate->clone()->startOf($this->viewMode === 'month' ? 'month' : 'week')->startOfWeek();
        $endDate = $this->currentDate->clone()->endOf($this->viewMode === 'month' ? 'month' : 'week')->endOfWeek();

        $cacheKey = "calendar_events_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
            return EventRecurrence::query()
                ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
                ->with([
                    'event' => fn($q) => $q->select(['id', 'name', 'status']),
                ])
                ->whereHas('event', fn($q) => $q->where('status', EventApprovalStatus::APPROVED))
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->orderBy('time_start')
                ->get();
        });
    }

    /**
     * Clear calendar cache for a specific date range.
     */
    public static function clearCache(?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        if ($startDate && $endDate) {
            $cacheKey = "calendar_events_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
            Cache::forget($cacheKey);
        }
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
