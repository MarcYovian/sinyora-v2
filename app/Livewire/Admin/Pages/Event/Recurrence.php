<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\Event;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Recurrence extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public Event $event;
    public array $occurrences = [];
    public string $search = '';
    public string $correlationId = '';

    public string $new_date = '';
    public string $new_start_time = '08:00';
    public string $new_end_time = '17:00';

    /**
     * Mount the component.
     */
    public function mount(Event $event): void
    {
        $this->authorize('access', 'admin.events.recurrences.index');
        $this->correlationId = Str::uuid()->toString();

        $this->event = $event;
        $this->loadOccurrences();
    }

    /**
     * Load event occurrences with optional search filter.
     */
    public function loadOccurrences(): void
    {
        $this->occurrences = $this->event->eventRecurrences()
            ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
            ->when($this->search, function ($query) {
                $query->where('date', 'like', '%' . $this->search . '%');
            })
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->date->format('Y-m-d'),
                    'time_start' => $item->time_start->format('H:i'),
                    'time_end' => $item->time_end->format('H:i'),
                ];
            })->toArray();
    }

    /**
     * Save an occurrence at the given index.
     */
    public function saveOccurrence(int $index): void
    {
        try {
            $this->authorize('access', 'admin.events.recurrences.edit');

            $data = $this->occurrences[$index];

            $this->validate([
                "occurrences.{$index}.date" => ['required', 'date'],
                "occurrences.{$index}.time_start" => ['required', 'date_format:H:i'],
                "occurrences.{$index}.time_end" => ['required', 'date_format:H:i', "after:occurrences.{$index}.time_start"],
            ]);

            $this->validateNoConflict(
                $data['id'],
                $data['date'],
                $data['time_start'],
                $data['time_end'],
                $this->event->locations->pluck('id')->toArray()
            );

            $occurrence = EventRecurrence::find($data['id']);

            if (!$occurrence) {
                flash()->error('Occurrence not found.');
                return;
            }

            $occurrence->update([
                'date' => $data['date'],
                'time_start' => $data['time_start'],
                'time_end' => $data['time_end'],
            ]);

            Log::info('Event occurrence updated', [
                'user_id' => Auth::id(),
                'occurrence_id' => $data['id'],
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
            ]);

            flash()->success('Data has been saved successfully!', ['timeOut' => 1500]);
            $this->loadOccurrences();
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized occurrence save attempt', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to edit recurrences.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to save occurrence', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the occurrence. #{$this->correlationId}");
        }
    }

    /**
     * Delete an occurrence by ID.
     */
    public function deleteOccurrence(int $id): void
    {
        try {
            $this->authorize('access', 'admin.events.recurrences.destroy');

            $occurrence = EventRecurrence::find($id);

            if (!$occurrence) {
                flash()->error('Occurrence not found.');
                return;
            }

            $occurrence->delete();

            Log::info('Event occurrence deleted', [
                'user_id' => Auth::id(),
                'occurrence_id' => $id,
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
            ]);

            flash()->success('Data has been deleted successfully!', ['timeOut' => 1500]);
            $this->loadOccurrences();
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized occurrence delete attempt', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to delete recurrences.');
        } catch (\Exception $e) {
            Log::error('Failed to delete occurrence', [
                'user_id' => Auth::id(),
                'occurrence_id' => $id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the occurrence. #{$this->correlationId}");
        }
    }

    /**
     * Validate that the schedule does not conflict with existing approved events.
     */
    protected function validateNoConflict(
        int $id,
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
            ->where('id', '!=', $id)
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where([
                    ['time_start', '<', $endTime],
                    ['time_end', '>', $startTime]
                ]);
            })
            ->select('id')
            ->exists();

        if ($conflictExists) {
            Log::warning('Schedule conflict detected', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'correlation_id' => $this->correlationId,
            ]);

            $message = 'The schedule conflicts with an existing event on '
                . Carbon::parse($date)->format('M j, Y')
                . ' between ' . Carbon::parse($startTime)->format('g:i A')
                . ' and ' . Carbon::parse($endTime)->format('g:i A');

            flash()->error($message, []);
            throw ValidationException::withMessages([
                'conflict' => __($message),
            ]);
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.events.recurrences.index');

        return view('livewire.admin.pages.event.recurrence');
    }
}
