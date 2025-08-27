<?php

namespace App\Services;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Events\EventProposalCreated;
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
    ) {}

    public function createEvent(array $data)
    {
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
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }

    public function createEventForGuest(array $data)
    {
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
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }

    public function createEventFromDocument(Document $document, array $data, array $information)
    {
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
    }

    public function getEventById(?int $id): ?Event
    {
        return $this->eventRepository->findById($id)->load(['eventCategory', 'organization', 'locations', 'eventRecurrences']);
    }

    public function updateEvent(Event $event, array $data)
    {
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
                ];

                $eventUpdated = $this->eventRepository->update($event->id, $eventData);

                if (!$eventUpdated) {
                    throw new \Exception('Failed to update event.');
                }

                $event->locations()->sync($data['locations']);

                $this->eventRecurrenceRepository->sync($event, $recurrences);
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }

    public function deleteEvent(Event $event): bool
    {
        try {
            if ($event->status === EventApprovalStatus::APPROVED) {
                throw new \Exception('Cannot delete an approved event.');
            }
            if ($event->status === EventApprovalStatus::REJECTED) {
                throw new \Exception('Cannot delete a rejected event.');
            }
            if ($event->status === EventApprovalStatus::PENDING) {
                $event->eventRecurrences()->delete();
                $event->locations()->detach();
                $event->delete();
                return true;
            }
            throw new \Exception('Invalid event status for deletion.');
        } catch (\Throwable $th) {
            throw new \Exception('Failed to delete event: ' . $th->getMessage());
        }
    }

    public function approveEvent(Event $event): bool
    {
        if ($event->status !== EventApprovalStatus::PENDING) {
            throw new \Exception('Only pending events can be approved.');
        }

        $event->load(['eventRecurrences', 'locations']);
        $recurrences = $event->eventRecurrences->map(function ($recurrence) {
            return [
                'date' => $recurrence->date,
                'time_start' => $recurrence->time_start,
                'time_end' => $recurrence->time_end,
            ];
        })->toArray();

        $locations = $event->locations->pluck('id')->toArray();
        $conflicts = $this->eventRecurrenceRepository->findConflicts($recurrences, $locations);

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

        $event->status = EventApprovalStatus::APPROVED;

        return $event->save();
    }

    public function rejectEvent(Event $event): bool
    {
        if ($event->status !== EventApprovalStatus::PENDING) {
            throw new \Exception('Only pending events can be rejected.');
        }

        $event->status = EventApprovalStatus::REJECTED;

        return $event->save();
    }

    private function handleRecurrences(array $data): array
    {
        $startEvent = Carbon::parse($data['datetime_start']);
        $endEvent = Carbon::parse($data['datetime_end']);

        if ($data['recurrence_type'] === EventRecurrenceType::DAILY->value || $data['recurrence_type'] === EventRecurrenceType::CUSTOM->value) {
            return $this->generateSegmentsForOccurrence($startEvent, $endEvent);
        }

        $allRecurrences = [];
        $endRecurringDate = Carbon::parse($data['end_recurring'])->endOfDay(); // Ensure we include events on the last day.

        $period = CarbonPeriod::create(
            $startEvent,
            $this->getInverval($data['recurrence_type']),
            $endRecurringDate
        );

        $eventDuration = $startEvent->diff($endEvent);

        foreach ($period as $occurrenceStartDate) {
            // Calculate the end date for this specific occurrence by adding the original duration.
            $occurrenceEndDate = $occurrenceStartDate->copy()->add($eventDuration);

            // Generate and merge the date/time segments for this single occurrence.
            $segments = $this->generateSegmentsForOccurrence($occurrenceStartDate, $occurrenceEndDate);
            $allRecurrences = array_merge($allRecurrences, $segments);
        }

        return $allRecurrences;
    }

    private function handleCustomRecurrences($data)
    {
        $allRecurrences = [];

        foreach ($data['custom_schedules'] as $schedule) {
            $startDate = Carbon::parse($schedule['datetime_start']);
            $endDate = Carbon::parse($schedule['datetime_end']);

            $allRecurrences = array_merge($allRecurrences, $this->generateSegmentsForOccurrence($startDate, $endDate));
        }

        return $allRecurrences;
    }

    private function generateSegmentsForOccurrence(Carbon $start, Carbon $end): array
    {
        $segments = [];
        $currentDate = $start->copy();

        // Loop day by day from the start of the occurrence until the end.
        while ($currentDate->isBefore($end)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            // Determine the start time for this segment.
            // If it's the very first day, use the event's start time. Otherwise, it's the beginning of the day.
            $segmentStart = $currentDate->isSameDay($start) ? $start : $dayStart;

            // Determine the end time for this segment.
            // If it's the last day, use the event's end time. Otherwise, it's the end of the day.
            $segmentEnd = $currentDate->isSameDay($end) ? $end : $dayEnd;

            $segments[] = [
                'date' => $currentDate->format('Y-m-d'),
                'time_start' => $segmentStart->format('H:i:s'),
                'time_end' => $segmentEnd->format('H:i:s'),
            ];

            // Move to the start of the next day.
            $currentDate->addDay()->startOfDay();
        }

        return $segments;
    }

    private function getInverval(string $recurrenceType): string
    {
        return match ($recurrenceType) {
            EventRecurrenceType::WEEKLY->value => '1 week',
            EventRecurrenceType::BIWEEKLY->value => '2 weeks',
            EventRecurrenceType::MONTHLY->value => '1 month',
            default => '1 day', // Default fallback
        };
    }

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

    private function checkForConflicts(array $recurrences, array $locationIds): void
    {
        $conflicts = $this->eventRecurrenceRepository->findConflicts($recurrences, $locationIds);

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
