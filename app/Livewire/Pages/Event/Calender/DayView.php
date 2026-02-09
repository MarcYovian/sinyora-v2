<?php

namespace App\Livewire\Pages\Event\Calender;

use App\Enums\EventApprovalStatus;
use App\Livewire\Traits\CalendarLayoutTrait;
use App\Models\EventRecurrence;
use App\Services\BlendColorService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DayView extends Component
{
    use CalendarLayoutTrait;

    /**
     * Cache TTL in seconds (3 minutes).
     */
    private const CACHE_TTL = 180;

    public $date;

    /**
     * Get events for the day with caching and optimized eager loading.
     */
    #[Computed]
    public function events()
    {
        $dateString = $this->date->format('Y-m-d');
        $cacheKey = "day_view_events_{$dateString}";

        $eventRecurrences = Cache::remember($cacheKey, self::CACHE_TTL, function () {
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
                ->where('date', $this->date)
                ->whereHas('event', fn($q) => $q->where('status', EventApprovalStatus::APPROVED))
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
     * Clear cache for this day.
     */
    public static function clearCache(string $date): void
    {
        Cache::forget("day_view_events_{$date}");
    }

    public function render()
    {
        $events = $this->events;
        $this->calculateLayoutProperties($events);

        return view('livewire.pages.event.calender.day-view', [
            'eventsByDay' => $events,
        ]);
    }
}
