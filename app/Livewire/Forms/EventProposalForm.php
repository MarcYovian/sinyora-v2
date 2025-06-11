<?php

namespace App\Livewire\Forms;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\GuestSubmitter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EventProposalForm extends Form
{
    public string $guestName = '';
    public string $guestEmail = '';
    public string $guestPhone = '';
    public string $name = '';
    public string $description = '';
    public ?int $event_category_id = null;
    public ?int $organization_id = null;
    public ?string $start_datetime = '';
    public ?string $end_datetime = '';
    public array $locations = [];

    public function rules(): array
    {
        return [
            'guestName' => ['required', 'string', 'max:255'],
            'guestEmail' => ['required', 'email', 'max:255'],
            'guestPhone' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'locations' => ['required', 'array', 'min:1'],
        ];
    }

    public function store()
    {
        $this->validate();

        // Simpan data submitter
        $guest = GuestSubmitter::firstOrCreate(
            ['email' => $this->guestEmail],
            [
                'name' => $this->guestName,
                'phone_number' => $this->guestPhone,
            ]
        );

        $event = new Event([
            'name' => $this->name,
            'description' => $this->description,
            'start_recurring' => Carbon::parse($this->start_datetime)->format('Y-m-d'),
            'end_recurring' => $this->getRecurrenceEndDate(),
            'status' => EventApprovalStatus::PENDING,
            'recurrence_type' => EventRecurrenceType::DAILY,
            'organization_id' => $this->organization_id,
            'event_category_id' => $this->event_category_id,
        ]);

        $guest->events()->save($event);

        $event->locations()->sync($this->locations);
        $this->handleRecurrence($event);

        $this->reset();
    }

    protected function handleRecurrence(Event $event): void
    {
        $startDate = Carbon::parse($this->start_datetime)->format('Y-m-d');
        $endDate = Carbon::parse($this->end_datetime)->format('Y-m-d');
        $startTime = Carbon::parse($this->start_datetime)->format('H:i:s');
        $endTime = Carbon::parse($this->end_datetime)->format('H:i:s');

        $this->generateSingleRecurrence($event, $startDate, $endDate, $startTime, $endTime);
    }

    protected function generateSingleRecurrence(Event $event, $startDate, $endDate, $startTime, $endTime): void
    {
        $isSameDay = $startDate === $endDate;
        $this->generateDailyRecurrences(
            $event->id,
            $startDate,
            $startTime,
            !$isSameDay ? '23:59:59' : $endTime,
            $this->locations
        );

        if (!$isSameDay) {
            $currentDate = Carbon::parse($startDate)->addDay();
            $endDate = Carbon::parse($endDate);

            while ($currentDate->lte($endDate)) {
                $timeStart = $currentDate->isSameDay($endDate) ? '00:00:00' : ($currentDate->isBefore($endDate) ? '00:00:00' : $startTime);
                $timeEnd = $currentDate->isSameDay($endDate) ? $endTime : '23:59:59';

                $this->generateDailyRecurrences(
                    $event->id,
                    $currentDate->format('Y-m-d'),
                    $timeStart,
                    $timeEnd,
                    $this->locations
                );

                $currentDate->addDay();
            }
        }
    }

    protected function generateDailyRecurrences(
        int $eventId,
        string $date,
        string $timeStart,
        string $timeEnd,
        array $locations
    ): void {
        $this->validateNoConflict($date, $timeStart, $timeEnd, $locations);

        EventRecurrence::create([
            'event_id' => $eventId,
            'date' => $date,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
        ]);
    }

    protected function validateNoConflict(
        string $date,
        string $startTime,
        string $endTime,
        array $locations
    ): void {

        $conflictExists = EventRecurrence::whereHas('event.locations', function ($q) use ($locations) {
            $q->whereIn('locations.id', $locations);
        })->whereHas('event', function ($q) {
            $q->where('status', EventApprovalStatus::APPROVED);
        })
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where([
                    ['time_start', '<', $endTime],
                    ['time_end', '>', $startTime]
                ]);
            })
            ->select('id')
            ->exists();
        // dd($conflictExists, $startTime, $endTime, $date);
        if ($conflictExists) {
            Log::warning('There is a conflict', [
                'date' => $date,
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
            throw ValidationException::withMessages([
                'conflict' => __('The schedule conflicts with an existing event on :date between :start and :end', [
                    'date' => Carbon::parse($date)->format('M j, Y'),
                    'start' => Carbon::parse($startTime)->format('g:i A'),
                    'end' => Carbon::parse($endTime)->format('g:i A')
                ])
            ]);
        }
    }

    protected function getRecurrenceEndDate(): string
    {
        return $this->end_datetime;
    }

    protected function getDayOfWeek(): int
    {
        return Carbon::parse($this->start_datetime)->dayOfWeek;
    }
}
