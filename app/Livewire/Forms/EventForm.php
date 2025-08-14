<?php

namespace App\Livewire\Forms;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Exceptions\ScheduleConflictException;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\User;
use App\Repositories\Eloquent\EloquentBorrowingRepository;
use App\Repositories\Eloquent\EloquentEventRecurrenceRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\EventCreationService;
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
    protected $eventCreationService;
    public ?Event $event;


    // Form properties
    public string $name = '';
    public string $description = '';
    public ?int $event_category_id = null;
    public ?int $organization_id = null;
    public string $datetime_start = '';
    public string $datetime_end = '';
    public $recurrence_type = EventRecurrenceType::DAILY;
    public string $start_recurring = '';
    public string $end_recurring = '';
    public array $locations = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'event_category_id' => ['nullable', Rule::exists('event_categories', 'id')],
            'organization_id' => ['nullable', Rule::exists('organizations', 'id')],
            'datetime_start' => ['required', 'dateformat:Y-m-d\TH:i', 'before_or_equal:datetime_end'],
            'datetime_end' => ['required', 'dateformat:Y-m-d\TH:i', 'after_or_equal:datetime_start'],
            'recurrence_type' => ['required', Rule::in(EventRecurrenceType::values())],
            'start_recurring' => [
                Rule::requiredIf($this->recurrence_type !== EventRecurrenceType::CUSTOM && $this->recurrence_type !== EventRecurrenceType::DAILY),
                'date_format:Y-m-d'
            ],
            'end_recurring' => [
                Rule::requiredIf($this->recurrence_type !== EventRecurrenceType::CUSTOM && $this->recurrence_type !== EventRecurrenceType::DAILY),
                'date_format:Y-m-d',
                'after_or_equal:start_recurring'
            ],
            'locations' => ['required', 'array', 'min:1'],
            'locations.*' => [Rule::exists('locations', 'id')],
        ];
    }

    protected function messages(): array
    {
        return [
            'datetime_start.before_or_equal' => __('The start date must be before or equal to the end date.'),
            'datetime_end.after_or_equal' => __('The end date must be after or equal to the start date.'),
            'locations.required' => __('At least one location is required.'),
            'locations.min' => __('You must select at least one location.'),
            'locations.*.exists' => __('The selected location is invalid.'),
            'event_category_id.exists' => __('The selected event category is invalid.'),
            'organization_id.exists' => __('The selected organization is invalid.'),
            'recurrence_type.in' => __('The selected recurrence type is invalid.'),
            'start_recurring.required' => __('The start recurring date is required.'),
            'end_recurring.required' => __('The end recurring date is required.'),
            'end_recurring.after_or_equal' => __('The end recurring date must be after or equal to the start recurring date.'),
            'datetime_start.date_format' => __('The start date must be in the format Y-m-d\TH:i.'),
            'datetime_end.date_format' => __('The end date must be in the format Y-m-d\TH:i.'),
            'name.required' => __('The event name is required.'),
            'name.string' => __('The event name must be a string.'),
            'name.max' => __('The event name may not be greater than 255 characters.'),
            'description.string' => __('The event description must be a string.'),
            'description.max' => __('The event description may not be greater than 65535 characters.'),
            'event_category_id.required' => __('The event category is required.'),
            'event_category_id.integer' => __('The event category must be an integer.'),
            'organization_id.required' => __('The organization is required.'),
            'organization_id.integer' => __('The organization must be an integer.'),
            'recurrence_type.required' => __('The recurrence type is required.'),
            'recurrence_type.string' => __('The recurrence type must be a string.'),
            'recurrence_type.max' => __('The recurrence type may not be greater than 255 characters.'),
            'start_recurring.date_format' => __('The start recurring date must be in the format Y-m-d.'),
            'end_recurring.date_format' => __('The end recurring date must be in the format Y-m-d.'),
            'start_recurring.after_or_equal' => __('The start recurring date must be after or equal to the start date.'),
            'locations.array' => __('The locations must be an array.'),
        ];
    }

    public function __construct(
        \Livewire\Component $component,
        $propertyName
    ) {
        parent::__construct($component, $propertyName);
        $this->eventCreationService = new EventCreationService(
            new EloquentEventRepository(),
            new EloquentEventRecurrenceRepository(),
            new EloquentUserRepository(),
            new EloquentBorrowingRepository()
        );
    }

    public function setEvent(?Event $event = null): void
    {
        $this->event = $event;
    }

    public function store(): void
    {
        $validated = $this->validate();

        try {
            $this->eventCreationService->createEvent($validated);
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => __('Failed to create event. Please try again.')]);
        }

        // $this->validate();

        // DB::transaction(function () {
        //     $admin = User::find(Auth::id());

        //     $event = $this->eventRepository->create([
        //         'name' => $this->name,
        //         'description' => $this->description,
        //         'start_recurring' => $this->start_recurring,
        //         'end_recurring' => $this->end_recurring,
        //         'status' => EventApprovalStatus::PENDING,
        //         'recurrence_type' => $this->recurrence_type,
        //         'organization_id' => $this->organization_id,
        //         'event_category_id' => $this->event_category_id,
        //     ]);

        //     $admin->events()->save($event);

        //     $event->locations()->sync($this->locations);
        //     $this->handleRecurrence($event);

        //     $this->reset();
        // });
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
