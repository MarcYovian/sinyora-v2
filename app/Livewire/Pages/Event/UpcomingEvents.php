<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\EventRecurrence;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UpcomingEvents extends Component
{
    /**
     * Cache key for upcoming events.
     */
    private const CACHE_KEY = 'upcoming_events_homepage';

    /**
     * Cache TTL in seconds (5 minutes).
     */
    private const CACHE_TTL = 300;

    /**
     * Get the upcoming events with caching and optimized eager loading.
     * Using Livewire computed property for additional in-request caching.
     *
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    public function events()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return EventRecurrence::query()
                // Select only needed columns from event_recurrences table
                ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
                // Optimized eager loading with column constraints
                ->with([
                    // Load event with only necessary columns
                    'event' => fn($query) => $query->select([
                        'id',
                        'name',
                        'description',
                        'event_category_id',
                    ]),
                    // Load event category with only id and name
                    'event.eventCategory' => fn($query) => $query->select([
                        'id',
                        'name',
                    ]),
                    // Load locations with only id and name via pivot
                    'event.locations' => fn($query) => $query->select([
                        'locations.id',
                        'locations.name',
                    ]),
                ])
                // Filter by approved events only
                ->whereHas('event', fn($q) => $q->where('status', EventApprovalStatus::APPROVED))
                // Only future events
                ->where('date', '>=', now()->startOfDay())
                // Order by date ascending
                ->orderBy('date')
                ->orderBy('time_start')
                // Limit to 6 events
                ->limit(6)
                ->get();
        });
    }

    /**
     * Clear the upcoming events cache.
     * Call this method when events are created, updated, or deleted.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Placeholder view for lazy loading.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function placeholder()
    {
        return view('livewire.loader.upcoming-events-loader');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.pages.event.upcoming-events');
    }
}
