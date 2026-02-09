<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MonthView extends Component
{
    /**
     * Cache TTL in seconds (3 minutes).
     */
    private const CACHE_TTL = 180;

    public $year;
    public $month;

    /**
     * Get the current date as Carbon instance.
     */
    #[Computed]
    public function currentDate()
    {
        return Carbon::create($this->year, $this->month, 1);
    }

    /**
     * Get events for the month with caching and optimized eager loading.
     */
    #[Computed]
    public function events()
    {
        $startDate = $this->currentDate->clone()->startOf('month')->startOfWeek();
        $endDate = $this->currentDate->clone()->endOf('month')->endOfWeek();

        $cacheKey = "month_view_events_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        $eventRecurrences = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
            return EventRecurrence::query()
                ->select(['id', 'event_id', 'date', 'time_start'])
                ->with([
                    'event' => fn($q) => $q->select(['id', 'name', 'event_category_id']),
                    'event.eventCategory' => fn($q) => $q->select(['id', 'name', 'color']),
                    'event.locations' => fn($q) => $q->select(['locations.id', 'locations.name', 'locations.color']),
                ])
                ->whereHas('event', fn($q) => $q->where('status', EventApprovalStatus::APPROVED))
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('time_start')
                ->get();
        });

        // Compute background colors (this is fast, doesn't need caching)
        $eventRecurrences->each(function ($recurrence) {
            if ($recurrence->event) {
                $locationColors = $recurrence->event->locations->pluck('color')->filter()->all();
                $recurrence->event->computed_background_color = BlendColorService::blend($locationColors);
            }
        });

        return $eventRecurrences;
    }

    /**
     * Clear cache for this month range.
     */
    public static function clearCache(string $startDate, string $endDate): void
    {
        Cache::forget("month_view_events_{$startDate}_{$endDate}");
    }

    public function render()
    {
        $eventByDay = $this->events->groupBy(fn($item) => $item->date->format('Y-m-d'));

        return view('livewire.pages.event.calender.month-view', [
            'eventsByDay' => $eventByDay,
        ]);
    }
}
