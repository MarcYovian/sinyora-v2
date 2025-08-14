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

    public function setEvent(?int $id = null): void
    {
        $this->event = $this->eventCreationService->getEventById($id);
        // dd($this->event->toArray());
        if ($this->event) {
            $this->name = $this->event->name;
            $this->description = $this->event->description;
            $this->event_category_id = $this->event->event_category_id;
            $this->organization_id = $this->event->organization_id;
            $firstRecurrence = $this->event->firstRecurrence;

            if ($firstRecurrence) {
                $this->datetime_start = Carbon::parse(
                    $firstRecurrence->date->format('Y-m-d') . ' ' . $firstRecurrence->time_start->format('H:i')
                )->format('Y-m-d\TH:i');

                $continuousEndDate = $this->event->getContinuousEndDate();
                if ($continuousEndDate) {
                    $this->datetime_end = $continuousEndDate->format('Y-m-d\TH:i');
                }
            } else {
                // Jika tidak ada jadwal sama sekali, kosongkan
                $this->datetime_start = '';
                $this->datetime_end = '';
            }

            $this->recurrence_type = $this->event->recurrence_type;

            if ($this->recurrence_type === EventRecurrenceType::WEEKLY || $this->recurrence_type === EventRecurrenceType::BIWEEKLY || $this->recurrence_type === EventRecurrenceType::MONTHLY) {
                $this->start_recurring = Carbon::parse($this->event->start_recurring)->format('Y-m-d');
                $this->end_recurring = Carbon::parse($this->event->end_recurring)->format('Y-m-d');
            } else {
                $this->start_recurring = '';
                $this->end_recurring = '';
            }

            $this->locations = $this->event->locations()->pluck('locations.id')->toArray();
        }
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
    }

    public function update(): void
    {
        $validated = $this->validate();

        try {
            $this->eventCreationService->updateEvent($this->event, $validated);
            $this->reset();
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => __('Failed to update event. Please try again.')]);
        }
    }

    public function delete(): void
    {
        if (!$this->event) {
            throw ValidationException::withMessages(['error' => __('Event not found.')]);
        }

        try {
            $this->eventCreationService->deleteEvent($this->event);
            $this->reset();
        } catch (\Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }

    public function approve()
    {
        try {
            $this->eventCreationService->approveEvent($this->event);
            $this->reset();
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }

    public function reject()
    {
        try {
            $this->eventCreationService->rejectEvent($this->event);
            $this->reset();
        } catch (\Exception $e) {
            Log::error('Error rejecting event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }
}
