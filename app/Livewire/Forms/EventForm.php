<?php

namespace App\Livewire\Forms;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Models\Event;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EventForm extends Form
{
    public ?Event $event;

    // Form properties
    public string $name = '';
    public string $description = '';
    public string $start_datetime = '';
    public string $end_datetime = '';
    public $recurrence_type = EventRecurrenceType::DAILY;
    public ?int $organization_id = null;
    public ?int $event_category_id = null;
    public array $locations = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'start_datetime' => ['required', 'dateformat:Y-m-d\TH:i', 'before_or_equal:end_datetime'],
            'end_datetime' => ['required', 'dateformat:Y-m-d\TH:i', 'after_or_equal:start_datetime'],
            'recurrence_type' => ['required', Rule::in(EventRecurrenceType::values())],
            'organization_id' => ['nullable', Rule::exists('organizations', 'id')],
            'event_category_id' => ['nullable', Rule::exists('event_categories', 'id')],
            'locations' => ['required', 'array', 'min:1'],
            'locations.*' => [Rule::exists('locations', 'id')],
        ];
    }

    public function setEvent(?Event $event = null): void
    {
        $this->event = $event;

        if ($event) {
            $this->name = $event->name;
            $this->description = $event->description;
            $this->recurrence_type = $event->recurrence_type;
            $this->organization_id = $event->organization_id;
            $this->event_category_id = $event->event_category_id;
            $this->locations = $event->locations->pluck('id')->toArray();

            if ($event->eventRecurrences()->exists()) {
                $dates = $event->eventRecurrences()->orderBy('date')->get();

                // Pastikan setidaknya ada 1 recurrence
                $startDate = $dates->first();
                $endDate = $startDate;

                // Cari rentang tanggal berurutan
                foreach ($dates as $key => $date) {
                    if ($key === 0) continue; // Lewatkan yang pertama

                    $prevDate = Carbon::parse($dates[$key - 1]->date);
                    $currentDate = Carbon::parse($date->date);

                    // Jika tanggal saat ini adalah besok dari tanggal sebelumnya
                    if ($prevDate->addDay()->equalTo($currentDate) && $date->time_start === '00:00:00' && $dates[$key - 1]->time_end === '23:59:59') {
                        $endDate = $date; // Update end date
                    } else {
                        break; // Berhenti jika tidak berurutan
                    }
                }

                // Format datetime
                $this->start_datetime = Carbon::parse($startDate->date . ' ' . $startDate->time_start)
                    ->format('Y-m-d\TH:i');
                $this->end_datetime = Carbon::parse($endDate->date . ' ' . $endDate->time_end)
                    ->format('Y-m-d\TH:i');
            }
        }
    }

    public function store(): void
    {
        $this->validate();

        DB::transaction(function () {
            $event = Event::create([
                'name' => $this->name,
                'description' => $this->description,
                'start_recurring' => Carbon::parse($this->start_datetime)->format('Y-m-d'),
                'end_recurring' => $this->getRecurrenceEndDate(),
                'status' => EventApprovalStatus::PENDING,
                'created_by' => Auth::id(),
                'recurrence_type' => $this->recurrence_type,
                'organization_id' => $this->organization_id,
                'event_category_id' => $this->event_category_id,
            ]);

            $event->locations()->sync($this->locations);
            $this->handleRecurrence($event);

            $this->reset();
        });
    }

    public function update(): void
    {
        $this->validate();

        DB::transaction(function () {
            $this->event->update([
                'name' => $this->name,
                'description' => $this->description,
                'start_recurring' => Carbon::parse($this->start_datetime)->format('Y-m-d'),
                'end_recurring' => $this->getRecurrenceEndDate(),
                'recurrence_type' => $this->recurrence_type,
                'organization_id' => $this->organization_id,
                'event_category_id' => $this->event_category_id,
            ]);

            $this->event->locations()->sync($this->locations);
            $this->event->eventRecurrences()->delete();
            $this->handleRecurrence($this->event);

            $this->reset();
        });
    }

    public function delete(): void
    {
        if (!$this->event) {
            return;
        }

        DB::transaction(function () {
            $this->event->eventRecurrences()->delete();
            $this->event->locations()->detach();
            $this->event->delete();
        });

        $this->reset();
    }

    public function approve()
    {
        if (!$this->event) {
            return;
        }

        $this->event->load('eventRecurrences');



        DB::transaction(function () {

            if ($this->event->eventRecurrences()->exists()) {
                foreach ($this->event->eventRecurrences as $eventRecurrence) {
                    $this->validateNoConflict(
                        $eventRecurrence->date,
                        $eventRecurrence->time_start,
                        $eventRecurrence->time_end,
                        $this->event->locations->pluck('id')->toArray()
                    );
                }
            }

            $this->event->update([
                'status' => EventApprovalStatus::APPROVED
            ]);
        });
    }

    public function reject()
    {
        if (!$this->event) {
            return;
        }
        $this->event->load('eventRecurrences');
        dd($this->event->toArray());

        DB::transaction(function () {
            $this->event->update([
                'status' => EventApprovalStatus::REJECTED
            ]);
        });
    }

    protected function handleRecurrence(Event $event): void
    {
        $startDate = Carbon::parse($this->start_datetime)->format('Y-m-d');
        $endDate = Carbon::parse($this->end_datetime)->format('Y-m-d');
        $startTime = Carbon::parse($this->start_datetime)->format('H:i:s');
        $endTime = Carbon::parse($this->end_datetime)->format('H:i:s');

        if ($this->recurrence_type === EventRecurrenceType::CUSTOM || $this->recurrence_type === EventRecurrenceType::DAILY) {
            $this->generateSingleRecurrence($event, $startDate, $endDate, $startTime, $endTime);
            return;
        }

        $this->generateRecurringEvents($event, $startDate, $endDate, $startTime, $endTime);
    }

    protected function generateRecurringEvents(Event $event, $startDate, $endDate, $startTime, $endTime): void
    {
        $isSameDay = $startDate === $endDate;
        $period = CarbonPeriod::create(
            $this->start_datetime,
            $this->getInterval(),
            $this->getRecurrenceEndDate()
        );

        foreach ($period as $date) {
            $this->generateDailyRecurrences(
                $event->id,
                $date->format('Y-m-d'),
                $startTime,
                !$isSameDay ? '23:59:59' : $endTime,
                $this->locations
            );

            if (!$isSameDay) {
                $currentDate = Carbon::parse($date->format('Y-m-d'))->addDay();
                $endDate = Carbon::parse($date)->addDays(Carbon::parse($this->start_datetime)->diffInDays($this->end_datetime));

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

    protected function getInterval(): string
    {
        return match ($this->recurrence_type) {
            EventRecurrenceType::DAILY => '1 day',
            EventRecurrenceType::WEEKLY => '1 week',
            EventRecurrenceType::BIWEEKLY => '2 weeks',
            EventRecurrenceType::MONTHLY => '1 month',
            default => '1 day',
        };
    }

    protected function getRecurrenceEndDate(): string
    {
        if ($this->recurrence_type === EventRecurrenceType::CUSTOM || $this->recurrence_type === EventRecurrenceType::DAILY) {
            return $this->end_datetime;
        }

        return Carbon::parse($this->end_datetime)
            ->addMonthsNoOverflow(3)
            ->next(Carbon::getDays()[$this->getDayOfWeek()])
            ->format('Y-m-d');
    }

    protected function getDayOfWeek(): int
    {
        return Carbon::parse($this->start_datetime)->dayOfWeek;
    }
}
