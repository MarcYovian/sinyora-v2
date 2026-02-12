<?php

namespace App\Services;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Events\EventApproved;
use App\Events\EventProposalCreated;
use App\Events\EventRejected;
use App\Exceptions\ScheduleConflictException;
use App\Mail\EventProposalSubmitted;
use App\Mail\NewEventProposalAdmin;
use App\Models\CustomLocation;
use App\Models\Document;
use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRecurrenceRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\LicensingDocumentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventCreationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected EventRepositoryInterface $eventRepository,
        protected EventRecurrenceRepositoryInterface $eventRecurrenceRepository,
        protected UserRepositoryInterface $userRepository,
        protected BorrowingRepositoryInterface $borrowingRepository,
        protected LicensingDocumentRepositoryInterface $licensingDocumentRepository,
        protected BorrowingManagementService $borrowingManagementService
    ) {
    }

    /**
     * Create a new event with recurrences and location associations.
     */
    public function createEvent(array $data): Event
    {
        Log::info('Creating new event', [
            'event_name' => $data['name'] ?? null,
            'recurrence_type' => $data['recurrence_type'] ?? null,
            'user_id' => Auth::id(),
        ]);

        if ($data['recurrence_type'] === EventRecurrenceType::CUSTOM->value) {
            $recurrences = $this->handleCustomRecurrences($data);
            $dates = array_column($recurrences, 'date');
            $start_recurring = !empty($dates) ? min($dates) : null;
            $end_recurring = !empty($dates) ? max($dates) : null;
        } else {
            $recurrences = $this->handleRecurrences($data);
            if ($data['recurrence_type'] === EventRecurrenceType::DAILY->value) {
                $start_recurring = Carbon::parse($data['datetime_start'])->format('Y-m-d');
                $end_recurring = Carbon::parse($data['datetime_end'])->format('Y-m-d');
            } else {
                $start_recurring = $data['start_recurring'];
                $end_recurring = $data['end_recurring'];
            }
        }

        $this->checkForConflicts($recurrences, $data['locations']);

        return DB::transaction(function () use ($data, $start_recurring, $end_recurring, $recurrences) {
            try {
                $user = $this->userRepository->findById(Auth::id());

                $eventData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'start_recurring' => $start_recurring,
                    'end_recurring' => $end_recurring,
                    'status' => EventApprovalStatus::PENDING,
                    'recurrence_type' => $data['recurrence_type'],
                    'organization_id' => $data['organization_id'],
                    'event_category_id' => $data['event_category_id'],
                ];

                $event = $this->eventRepository->create($user, new Event($eventData));

                $event->locations()->sync($data['locations']);

                $this->eventRecurrenceRepository->create($event, $recurrences);

                Log::info('Event created successfully', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'recurrences_count' => count($recurrences),
                    'user_id' => Auth::id(),
                ]);

                return $event;
            } catch (\Throwable $th) {
                Log::error('Failed to create event', [
                    'event_name' => $data['name'] ?? null,
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Create an event submitted by a guest user.
     */
    public function createEventForGuest(array $data): Event
    {
        Log::info('Creating event for guest', [
            'guest_email' => $data['guestEmail'] ?? null,
            'event_name' => $data['name'] ?? null,
        ]);

        $classifier = new EventCategoryClassifier();
        $data['event_category_id'] = $classifier->classify($data['name'], $data['description']);
        $data['recurrence_type'] = EventRecurrenceType::DAILY->value;

        $recurrences = $this->handleRecurrences($data);

        $this->checkForConflicts($recurrences, $data['locations']);

        return DB::transaction(function () use ($data, $recurrences) {
            try {
                $guest = GuestSubmitter::firstOrCreate(
                    ['email' => $data['guestEmail']],
                    [
                        'name' => $data['guestName'],
                        'phone_number' => $data['guestPhone'],
                    ]
                );

                $eventData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'start_recurring' => Carbon::parse($data['datetime_start'])->format('Y-m-d'),
                    'end_recurring' => Carbon::parse($data['datetime_end'])->format('Y-m-d'),
                    'status' => EventApprovalStatus::PENDING,
                    'recurrence_type' => $data['recurrence_type'],
                    'organization_id' => $data['organization_id'],
                    'event_category_id' => $data['event_category_id'],
                ];

                $event = $this->eventRepository->create($guest, new Event($eventData));

                $event->locations()->sync($data['locations']);

                $this->eventRecurrenceRepository->create($event, $recurrences);

                if ($data['enableBorrowing'] && !empty($data['assets'])) {
                    $borrowingData = [
                        'start_datetime' => $data['datetime_start'],
                        'end_datetime' => $data['datetime_end'],
                        'notes' => $data['borrowingNotes'] ?? '',
                        'borrower' => $data['guestName'] ?? '',
                        'borrower_phone' => $data['guestPhone'] ?? '',
                        'assets' => $data['assets'] ?? [],
                        'creator_id' => $guest->id,
                        'creator_type' => $guest->getMorphClass(),
                        'borrowable_id' => $event->id,
                        'borrowable_type' => $event->getMorphClass(),
                    ];

                    $this->borrowingRepository->create($borrowingData);
                }

                EventProposalCreated::dispatch($guest, $event);

                Log::info('Guest event created successfully', [
                    'event_id' => $event->id,
                    'guest_email' => $data['guestEmail'] ?? null,
                ]);

                return $event;
            } catch (\Throwable $th) {
                Log::error('Failed to create guest event', [
                    'guest_email' => $data['guestEmail'] ?? null,
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Create events from a parsed document with licensing and optional borrowing.
     */
    public function createEventFromDocument(Document $document, array $data, array $information): void
    {
        Log::info('Creating events from document', [
            'document_id' => $document->id,
            'dates_count' => count(data_get($data, 'parsed_dates.dates', [])),
            'user_id' => Auth::id(),
        ]);

        $user = $this->userRepository->findById(Auth::id());
        $organizationId = data_get($information, 'final_organization_id');

        foreach (data_get($data, 'parsed_dates.dates', []) as $date) {
            $startDateTime = Carbon::parse($date['start']);
            $endDateTime = Carbon::parse($date['end']);

            $licensing = $this->licensingDocumentRepository->create([
                'description' => $data['eventName'],
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
            ]);

            $event = $this->eventRepository->create($user, new Event([
                'name' => $data['eventName'],
                'start_recurring' => $startDateTime->format('Y-m-d'),
                'end_recurring' => $endDateTime->format('Y-m-d'),
                'status' => EventApprovalStatus::PENDING,
                'recurrence_type' => EventRecurrenceType::DAILY->value,
                'organization_id' => $organizationId,
                'event_category_id' => $data['fivetask_categories']['id'],
                'document_typable_id' => $licensing->id,
                'document_typable_type' => $licensing->getMorphClass(),
            ]));

            $this->syncLocationsToEvent($event, $data['location_data']);

            $document->licensingDocuments()->attach($licensing->id);

            $recurrenceData = $this->generateSegmentsForOccurrence($startDateTime, $endDateTime);
            $this->eventRecurrenceRepository->create($event, $recurrenceData);

            if (!empty($data['equipment'])) {
                $this->borrowingManagementService->createBorrowingForEvent(
                    event: $event,
                    eventData: $data,
                    start: $startDateTime,
                    end: $endDateTime,
                    documentable: $licensing
                );
            }
        }

        Log::info('Events created from document successfully', [
            'document_id' => $document->id,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Get an event by ID with its relations.
     */
    public function getEventById(?int $id): ?Event
    {
        if ($id === null) {
            return null;
        }

        $event = $this->eventRepository->findById($id);

        return $event?->load(['eventCategory', 'organization', 'locations', 'eventRecurrences']);
    }

    /**
     * Update an existing event with new data, recurrences, and locations.
     */
    public function updateEvent(Event $event, array $data): Event
    {
        Log::info('Updating event', [
            'event_id' => $event->id,
            'event_name' => $data['name'] ?? null,
            'user_id' => Auth::id(),
        ]);

        if ($data['recurrence_type'] === EventRecurrenceType::CUSTOM->value) {
            $recurrences = $this->handleCustomRecurrences($data);
            $dates = array_column($recurrences, 'date');
            $start_recurring = !empty($dates) ? min($dates) : null;
            $end_recurring = !empty($dates) ? max($dates) : null;
        } else {
            $recurrences = $this->handleRecurrences($data);
            if ($data['recurrence_type'] === EventRecurrenceType::DAILY->value) {
                $start_recurring = Carbon::parse($data['datetime_start'])->format('Y-m-d');
                $end_recurring = Carbon::parse($data['datetime_end'])->format('Y-m-d');
            } else {
                $start_recurring = $data['start_recurring'];
                $end_recurring = $data['end_recurring'];
            }
        }

        $this->checkForConflicts($recurrences, $data['locations'], $event->id);

        return DB::transaction(function () use ($event, $data, $start_recurring, $end_recurring, $recurrences) {
            try {
                $eventData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'start_recurring' => $start_recurring,
                    'end_recurring' => $end_recurring,
                    'recurrence_type' => $data['recurrence_type'],
                    'organization_id' => $data['organization_id'],
                    'event_category_id' => $data['event_category_id'],
                    'status' => EventApprovalStatus::PENDING,
                    'rejection_reason' => null,
                ];

                $eventUpdated = $this->eventRepository->update($event->id, $eventData);

                if (!$eventUpdated) {
                    throw new \Exception('Failed to update event.');
                }

                $event->locations()->sync($data['locations']);

                $this->eventRecurrenceRepository->sync($event, $recurrences);

                Log::info('Event updated successfully', [
                    'event_id' => $event->id,
                    'recurrences_count' => count($recurrences),
                    'user_id' => Auth::id(),
                ]);

                return $event->fresh();
            } catch (\Throwable $th) {
                Log::error('Failed to update event', [
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Delete an event (only pending events can be deleted).
     */
    public function deleteEvent(Event $event): bool
    {
        Log::info('Deleting event', [
            'event_id' => $event->id,
            'status' => $event->status->value ?? $event->status,
            'user_id' => Auth::id(),
        ]);

        if ($event->status !== EventApprovalStatus::PENDING) {
            throw new \Exception('Only pending events can be deleted. Current status: ' . ($event->status->value ?? $event->status));
        }

        return DB::transaction(function () use ($event) {
            try {
                $event->eventRecurrences()->delete();
                $event->locations()->detach();
                $event->delete();

                Log::info('Event deleted successfully', [
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                ]);

                return true;
            } catch (\Throwable $th) {
                Log::error('Failed to delete event', [
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Approve a pending event after checking for schedule conflicts.
     */
    public function approveEvent(Event $event): bool
    {
        Log::info('Approving event', [
            'event_id' => $event->id,
            'user_id' => Auth::id(),
        ]);

        if ($event->status !== EventApprovalStatus::PENDING) {
            throw new \Exception('Only pending events can be approved.');
        }

        try {
            $event->load(['eventRecurrences', 'locations']);
            $recurrences = $event->eventRecurrences->map(function ($recurrence) {
                return [
                    'date' => $recurrence->date,
                    'time_start' => $recurrence->time_start,
                    'time_end' => $recurrence->time_end,
                ];
            })->toArray();

            $locations = $event->locations->pluck('id')->toArray();
            $this->checkForConflicts($recurrences, $locations);

            $updated = $this->eventRepository->changeStatus($event, EventApprovalStatus::APPROVED);
            if (!$updated) {
                throw new \Exception('Failed to update event status.');
            }

            EventApproved::dispatch($event->creator, $event);

            Log::info('Event approved successfully', [
                'event_id' => $event->id,
                'approved_by' => Auth::id(),
            ]);

            return $updated;
        } catch (ScheduleConflictException $e) {
            Log::warning('Event approval blocked by schedule conflict', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'conflict' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Failed to approve event', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);
            throw new \Exception('Failed to approve event: ' . $th->getMessage(), previous: $th);
        }
    }

    /**
     * Reject a pending event with a reason.
     */
    public function rejectEvent(Event $event, string $rejectionReason): bool
    {
        Log::info('Rejecting event', [
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'reason' => $rejectionReason,
        ]);

        if ($event->status !== EventApprovalStatus::PENDING) {
            throw new \Exception('Only pending events can be rejected.');
        }

        try {
            $updated = $this->eventRepository->changeStatus($event, EventApprovalStatus::REJECTED, $rejectionReason);
            if (!$updated) {
                throw new \Exception('Failed to update event status.');
            }

            EventRejected::dispatch($event->creator, $event, $rejectionReason);

            Log::info('Event rejected successfully', [
                'event_id' => $event->id,
                'rejected_by' => Auth::id(),
                'reason' => $rejectionReason,
            ]);

            return $updated;
        } catch (\Throwable $th) {
            Log::error('Failed to reject event', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);
            throw new \Exception('Failed to reject event: ' . $th->getMessage(), previous: $th);
        }
    }

    /**
     * Generate recurrence data for standard (non-custom) recurrence types.
     */
    private function handleRecurrences(array $data): array
    {
        $startEvent = Carbon::parse($data['datetime_start']);
        $endEvent = Carbon::parse($data['datetime_end']);

        if ($data['recurrence_type'] === EventRecurrenceType::DAILY->value || $data['recurrence_type'] === EventRecurrenceType::CUSTOM->value) {
            return $this->generateSegmentsForOccurrence($startEvent, $endEvent);
        }

        $allRecurrences = [];
        $endRecurringDate = Carbon::parse($data['end_recurring'])->endOfDay();

        $period = CarbonPeriod::create(
            $startEvent,
            $this->getInterval($data['recurrence_type']),
            $endRecurringDate
        );

        $eventDuration = $startEvent->diff($endEvent);

        foreach ($period as $occurrenceStartDate) {
            $occurrenceEndDate = $occurrenceStartDate->copy()->add($eventDuration);

            $segments = $this->generateSegmentsForOccurrence($occurrenceStartDate, $occurrenceEndDate);
            $allRecurrences = array_merge($allRecurrences, $segments);
        }

        return $allRecurrences;
    }

    /**
     * Generate recurrence data for custom schedule entries.
     */
    private function handleCustomRecurrences(array $data): array
    {
        $allRecurrences = [];

        foreach ($data['custom_schedules'] as $schedule) {
            $startDate = Carbon::parse($schedule['datetime_start']);
            $endDate = Carbon::parse($schedule['datetime_end']);

            $allRecurrences = array_merge($allRecurrences, $this->generateSegmentsForOccurrence($startDate, $endDate));
        }

        return $allRecurrences;
    }

    /**
     * Generate day-by-day time segments for a single occurrence spanning start to end.
     */
    private function generateSegmentsForOccurrence(Carbon $start, Carbon $end): array
    {
        $segments = [];
        $currentDate = $start->copy();

        while ($currentDate->isBefore($end)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            $segmentStart = $currentDate->isSameDay($start) ? $start : $dayStart;
            $segmentEnd = $currentDate->isSameDay($end) ? $end : $dayEnd;

            $segments[] = [
                'date' => $currentDate->format('Y-m-d'),
                'time_start' => $segmentStart->format('H:i:s'),
                'time_end' => $segmentEnd->format('H:i:s'),
            ];

            $currentDate->addDay()->startOfDay();
        }

        return $segments;
    }

    /**
     * Get the interval string for CarbonPeriod based on recurrence type.
     */
    private function getInterval(string $recurrenceType): string
    {
        return match ($recurrenceType) {
            EventRecurrenceType::WEEKLY->value => '1 week',
            EventRecurrenceType::BIWEEKLY->value => '2 weeks',
            EventRecurrenceType::MONTHLY->value => '1 month',
            default => '1 day',
        };
    }

    /**
     * Sync both standard and custom locations to an event.
     */
    private function syncLocationsToEvent(Event $event, array $locationData): void
    {
        $locationIds = collect($locationData)
            ->whereNotNull('location_id')
            ->where('source', 'location')
            ->where('match_status', 'matched')
            ->pluck('location_id')
            ->filter();

        if ($locationIds->isNotEmpty()) {
            $event->locations()->sync($locationIds->all());
        }

        $customLocations = collect($locationData)
            ->where('source', 'custom')
            ->all();

        foreach ($customLocations as $loc) {
            $customLocation = CustomLocation::firstOrCreate(['name' => $loc['name']]);
            $event->customLocations()->attach($customLocation->id);
        }
    }

    /**
     * Check for scheduling conflicts with existing approved events at the same locations.
     *
     * @throws ScheduleConflictException
     */
    private function checkForConflicts(array $recurrences, array $locationIds, ?int $excludeEventId = null): void
    {
        $conflicts = $this->eventRecurrenceRepository->findConflicts($recurrences, $locationIds, $excludeEventId);

        if ($conflicts->count() > 0) {
            $firstConflict = $conflicts->first();
            $errorMessage = sprintf(
                'Sudah ada kegiatan lain pada tanggal %s antara jam %s - %s.',
                Carbon::parse($firstConflict->date)->isoFormat('D MMMM YYYY'),
                Carbon::parse($firstConflict->time_start)->format('H:i'),
                Carbon::parse($firstConflict->time_end)->format('H:i')
            );

            throw new ScheduleConflictException($errorMessage);
        }
    }
}
