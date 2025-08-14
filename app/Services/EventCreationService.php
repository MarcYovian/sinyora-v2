<?php

namespace App\Services;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Exceptions\ScheduleConflictException;
use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRecurrenceRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventCreationService
{
    protected $eventRepository;
    protected $eventRecurrenceRepository;
    protected $userRepository;
    protected $borrowingRepository;
    /**
     * Create a new class instance.
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventRecurrenceRepositoryInterface $eventRecurrenceRepository,
        UserRepositoryInterface $userRepository,
        BorrowingRepositoryInterface $borrowingRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventRecurrenceRepository = $eventRecurrenceRepository;
        $this->userRepository = $userRepository;
        $this->borrowingRepository = $borrowingRepository;
    }

    public function createEvent(array $data)
    {
        $recurrences = $this->handleRecurrences($data);

        $conflicts = $this->eventRecurrenceRepository->findConflicts($recurrences, $data['locations']);
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
        $isDailyOrCustom = $data['recurrence_type'] === EventRecurrenceType::DAILY->value || $data['recurrence_type'] === EventRecurrenceType::CUSTOM->value;

        return DB::transaction(function () use ($data, $isDailyOrCustom, $recurrences) {
            try {
                $user = $this->userRepository->findById(Auth::id());

                $eventData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'start_recurring' => $isDailyOrCustom ? Carbon::parse($data['datetime_start'])->format('Y-m-d') : $data['start_recurring'],
                    'end_recurring' => $isDailyOrCustom ? Carbon::parse($data['datetime_end'])->format('Y-m-d') : $data['end_recurring'],
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

        $conflicts = $this->eventRecurrenceRepository->findConflicts($recurrences, $data['locations']);
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
                    $this->borrowingRepository->create($guest, $event, [
                        'start_datetime' => $data['datetime_start'],
                        'end_datetime' => $data['datetime_end'],
                        'notes' => $data['notes'] ?? '',
                        'guestName' => $data['guestName'],
                        'guestPhone' => $data['guestPhone'],
                        'assets' => $data['assets'] ?? [],
                    ]);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        });
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
}
