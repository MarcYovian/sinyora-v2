<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Models\Event;
use App\Models\EventRecurrence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Recurrence extends Component
{
    #[Layout('layouts.app')]

    public Event $event;
    public $occurrences = [];
    public $search;

    public $new_date;
    public $new_start_time = '08:00';
    public $new_end_time = '17:00';

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadOccurrences();
        // dd($this->occurrences);
    }

    public function loadOccurrences()
    {
        $this->occurrences = $this->event->eventRecurrences()
            ->when($this->search, function ($query) {
                $query->where('date', 'like', '%' . $this->search . '%');
            })->orderBy('date')
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

    public function saveOccurrence($index)
    {
        $data = $this->occurrences[$index];


        $validated = $this->validate([
            "occurrences.{$index}.date" => ['required', 'date'],
            "occurrences.{$index}.time_start" => ['required', 'date_format:H:i'],
            "occurrences.{$index}.time_end" => ['required', 'date_format:H:i', 'after:occurrences.{$index}.time_start'],
        ]);

        $this->validateNoConflict($data['id'], $data['date'], $data['time_start'], $data['time_end'], $this->event->locations->pluck('id')->toArray());

        $occurrence = EventRecurrence::find($data['id']);


        $occurrence->update([
            'date' => $data['date'],
            'time_start' => $data['time_start'],
            'time_end' => $data['time_end'],
        ]);
        toastr()->success('Data has been saved successfully!', ['timeOut' => 1500]);
        $this->loadOccurrences();
    }

    public function deleteOccurrence($id)
    {
        EventRecurrence::find($id)->delete();
        toastr()->success('Data has been deleted successfully!', ['timeOut' => 1500]);
        $this->loadOccurrences();
    }

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
        // dd($conflictExists, $startTime, $endTime, $date);
        if ($conflictExists) {
            Log::warning('There is a conflict', [
                'date' => $date,
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
            $message = 'The schedule conflicts with an existing event on ' . Carbon::parse($date)->format('M j, Y') . ' between ' . Carbon::parse($startTime)->format('g:i A') . ' and ' . Carbon::parse($endTime)->format('g:i A');
            toastr()->error($message, []);
            throw ValidationException::withMessages([
                'conflict' => __($message),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.event.recurrence');
    }
}
