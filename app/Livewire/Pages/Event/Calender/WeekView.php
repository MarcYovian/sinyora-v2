<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Livewire\Traits\CalendarLayoutTrait;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WeekView extends Component
{
    use CalendarLayoutTrait;

    /**
     * Cache TTL in seconds (3 minutes).
     */
    private const CACHE_TTL = 180;

    public $startOfWeek;

    /**
     * Get events for the week with caching and optimized eager loading.
     */
    #[Computed]
    public function events()
    {
        $endOfWeek = $this->startOfWeek->clone()->endOfWeek();
        $startString = $this->startOfWeek->format('Y-m-d');
        $endString = $endOfWeek->format('Y-m-d');

        $cacheKey = "week_view_events_{$startString}_{$endString}";

        $eventRecurrences = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($endOfWeek) {
            return EventRecurrence::query()
                ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
                ->with([
                    'event' => fn($q) => $q->select([
                        'id', 'name', 'organization_id', 'event_category_id',
                    ]),
                    'event.organization' => fn($q) => $q->select(['id', 'name']),
                    'event.eventCategory' => fn($q) => $q->select(['id', 'name', 'color']),
                    'event.locations' => fn($q) => $q->select(['locations.id', 'locations.name', 'locations.color']),
                    'event.customLocations' => fn($q) => $q->select(['custom_locations.id', 'custom_locations.address']),
                ])
                ->whereBetween('date', [$this->startOfWeek, $endOfWeek])
                ->whereHas('event', fn($q) => $q->where('status', EventApprovalStatus::APPROVED))
                ->orderBy('date')
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
     * Clear cache for this week range.
     */
    public static function clearCache(string $startDate, string $endDate): void
    {
        Cache::forget("week_view_events_{$startDate}_{$endDate}");
    }

    public function render()
    {
        $eventsByDay = $this->events->groupBy(fn($item) => $item->date->format('Y-m-d'));

        foreach ($eventsByDay as $day => $dayEvents) {
            $this->calculateLayoutProperties($dayEvents);
        }

        return view('livewire.pages.event.calender.week-view', [
            'eventsByDay' => $eventsByDay,
        ]);
    }
}
