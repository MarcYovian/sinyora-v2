<?php

namespace App\Livewire\Forms;

use App\Enums\EventRecurrenceType;
use App\Exceptions\ScheduleConflictException;
use App\Models\Event;
use App\Services\EventCreationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class EventForm extends Form
{
    public ?Event $event = null;

    // Form properties
    public string $name = '';
    public string $description = '';
    public ?int $event_category_id = null;
    public ?int $organization_id = null;
    public string $datetime_start = '';
    public string $datetime_end = '';
    public $recurrence_type = 'daily';
    public string $start_recurring = '';
    public string $end_recurring = '';
    public array $locations = [];
    public array $custom_schedules = [];
    public string $rejection_reason = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'event_category_id' => ['nullable', Rule::exists('event_categories', 'id')],
            'organization_id' => ['nullable', Rule::exists('organizations', 'id')],
            'datetime_start' => [
                Rule::requiredIf($this->recurrence_type !== EventRecurrenceType::CUSTOM->value),
                'nullable',
                'dateformat:Y-m-d\TH:i',
                'before_or_equal:datetime_end'
            ],
            'datetime_end' => [
                Rule::requiredIf($this->recurrence_type !== EventRecurrenceType::CUSTOM->value),
                'nullable',
                'dateformat:Y-m-d\TH:i',
                'after_or_equal:datetime_start'
            ],
            'recurrence_type' => ['required', Rule::in(EventRecurrenceType::values())],
            'start_recurring' => [
                Rule::requiredIf(in_array($this->recurrence_type, [EventRecurrenceType::WEEKLY->value, EventRecurrenceType::BIWEEKLY->value, EventRecurrenceType::MONTHLY->value])),
                'nullable',
                'date_format:Y-m-d'
            ],
            'end_recurring' => [
                Rule::requiredIf(in_array($this->recurrence_type, [EventRecurrenceType::WEEKLY->value, EventRecurrenceType::BIWEEKLY->value, EventRecurrenceType::MONTHLY->value])),
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:start_recurring'
            ],
            'locations' => ['required', 'array', 'min:1'],
            'locations.*' => [Rule::exists('locations', 'id')],
            'custom_schedules' => [
                Rule::when(fn() => $this->recurrence_type === EventRecurrenceType::CUSTOM->value, [
                    'required',
                    'array',
                    'min:1'
                ]),

            ],
            'custom_schedules.*.datetime_start' => [
                'required',
                'date_format:Y-m-d\TH:i',
                'before_or_equal:custom_schedules.*.datetime_end'
            ],
            'custom_schedules.*.datetime_end' => [
                'required',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:custom_schedules.*.datetime_start'
            ],
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
            'custom_schedules.array' => __('The custom schedules must be an array.'),
            'custom_schedules.required' => __('At least one custom schedule is required.'),
            'custom_schedules.min' => __('You must select at least one custom schedule.'),
            'custom_schedules.*.datetime_start.required' => __('The start date is required.'),
            'custom_schedules.*.datetime_start.date_format' => __('The start date must be in the format Y-m-d\TH:i.'),
            'custom_schedules.*.datetime_start.before_or_equal' => __('The start date must be before or equal to the end date.'),
            'custom_schedules.*.datetime_end.required' => __('The end date is required.'),
            'custom_schedules.*.datetime_end.date_format' => __('The end date must be in the format Y-m-d\TH:i.'),
            'custom_schedules.*.datetime_end.after_or_equal' => __('The end date must be after or equal to the start date.'),
        ];
    }

    /**
     * Set the form data from an existing event.
     */
    public function setEvent(?int $id = null): void
    {
        $this->event = app(EventCreationService::class)->getEventById($id);

        if ($this->event) {
            $this->name = $this->event->name;
            $this->description = $this->event->description ?? '';
            $this->event_category_id = $this->event->event_category_id;
            $this->organization_id = $this->event->organization_id;
            $this->recurrence_type = $this->event->recurrence_type->value;

            if ($this->recurrence_type === EventRecurrenceType::CUSTOM->value) {
                // Use already-loaded relation (no extra query) instead of ->eventRecurrences()
                $recurrences = $this->event->eventRecurrences
                    ->sortBy([['date', 'asc'], ['time_start', 'asc']])
                    ->values();

                $this->custom_schedules = [];
                $count = $recurrences->count();

                if ($count > 0) {
                    for ($i = 0; $i < $count; $i++) {
                        $blockStartRecurrence = $recurrences->get($i);
                        while (
                            ($i + 1) < $count &&
                            $recurrences->get($i)->time_end->format('H:i:s') === '23:59:59' &&
                            $recurrences->get($i + 1)->time_start->format('H:i:s') === '00:00:00' &&
                            $recurrences->get($i + 1)->date->isSameDay($recurrences->get($i)->date->copy()->addDay())
                        ) {
                            $i++;
                        }

                        $blockEndRecurrence = $recurrences->get($i);

                        $startDateTime = Carbon::parse($blockStartRecurrence->date->format('Y-m-d') . ' ' . $blockStartRecurrence->time_start->format('H:i'));
                        $endDateTime = Carbon::parse($blockEndRecurrence->date->format('Y-m-d') . ' ' . $blockEndRecurrence->time_end->format('H:i'));

                        $this->custom_schedules[] = [
                            'datetime_start' => $startDateTime->format('Y-m-d\TH:i'),
                            'datetime_end' => $endDateTime->format('Y-m-d\TH:i'),
                        ];
                    }
                }

                if (empty($this->custom_schedules)) {
                    $this->custom_schedules = [['datetime_start' => '', 'datetime_end' => '']];
                }
            } else {
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
                    $this->datetime_start = '';
                    $this->datetime_end = '';
                }
            }

            if (in_array($this->recurrence_type, [EventRecurrenceType::WEEKLY->value, EventRecurrenceType::BIWEEKLY->value, EventRecurrenceType::MONTHLY->value])) {
                $this->start_recurring = Carbon::parse($this->event->start_recurring)->format('Y-m-d');
                $this->end_recurring = Carbon::parse($this->event->end_recurring)->format('Y-m-d');
            } else {
                $this->start_recurring = '';
                $this->end_recurring = '';
            }

            // Use already-loaded relation instead of query
            $this->locations = $this->event->locations->pluck('id')->toArray();
        }
    }

    /**
     * Add a new custom schedule entry.
     */
    public function addCustomSchedule(): void
    {
        $this->custom_schedules[] = ['datetime_start' => '', 'datetime_end' => ''];
    }

    /**
     * Remove a custom schedule entry by index.
     */
    public function removeCustomSchedule(int $index): void
    {
        unset($this->custom_schedules[$index]);
        $this->custom_schedules = array_values($this->custom_schedules);
    }

    /**
     * Store a new event.
     */
    public function store(): void
    {
        $validated = $this->validate();

        Log::info('Event store validated data:', $validated);

        try {
            app(EventCreationService::class)->createEvent($validated);

            Log::info('Event created via form', [
                'event_name' => $validated['name'],
                'user_id' => auth()->id(),
            ]);

            $this->reset();
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error creating event', [
                'event_name' => $validated['name'] ?? 'unknown',
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages(['error' => __('Failed to create event. Please try again.')]);
        }
    }

    /**
     * Update an existing event.
     */
    public function update(): void
    {
        $validated = $this->validate();

        try {
            app(EventCreationService::class)->updateEvent($this->event, $validated);

            Log::info('Event updated via form', [
                'event_id' => $this->event?->id,
                'event_name' => $validated['name'],
                'user_id' => auth()->id(),
            ]);

            $this->reset();
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error updating event', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages(['error' => __('Failed to update event. Please try again.')]);
        }
    }

    /**
     * Delete the current event.
     */
    public function delete(): void
    {
        if (!$this->event) {
            throw ValidationException::withMessages(['error' => __('Event not found.')]);
        }

        try {
            app(EventCreationService::class)->deleteEvent($this->event);

            Log::info('Event deleted via form', [
                'event_id' => $this->event->id,
                'user_id' => auth()->id(),
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Error deleting event', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve the current event.
     */
    public function approve(): void
    {
        try {
            app(EventCreationService::class)->approveEvent($this->event);

            Log::info('Event approved via form', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
            ]);

            $this->reset();
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error approving event', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject the current event with a reason.
     */
    public function reject(): void
    {
        if ($this->rejection_reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => 'Rejection reason is required.']);
        }

        try {
            app(EventCreationService::class)->rejectEvent($this->event, $this->rejection_reason);

            Log::info('Event rejected via form', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
                'reason' => $this->rejection_reason,
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Error rejecting event', [
                'event_id' => $this->event?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reset the form to initial state.
     */
    public function resetForm(): void
    {
        $this->reset([
            'name', 'description', 'event_category_id', 'organization_id',
            'datetime_start', 'datetime_end', 'recurrence_type',
            'start_recurring', 'end_recurring', 'locations',
            'custom_schedules', 'rejection_reason',
        ]);
        $this->event = null;
        $this->resetErrorBag();
    }
}
