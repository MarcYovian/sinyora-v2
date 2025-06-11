<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\EventRecurrence;
use App\Models\GuestSubmitter;
use App\Models\Location;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Kalender Kegiatan')]

    public $events = [];

    use WithFileUploads;

    public EventProposalForm $form;
    public $document;

    public function updatedDocument()
    {
        $this->form->document = $this->document;
        $this->document = $this->form->document;
    }

    public function create()
    {
        $this->dispatch('open-modal', 'proposal-modal');
    }

    public function submitProposal()
    {
        toastr()->success('Proposal berhasil diajukan! Terima kasih atas partisipasi Anda.');
    }

    public function mount()
    {
        $this->events = EventRecurrence::with(['event:id,name,organization_id,status,event_category_id', 'event.organization:id,code', 'event.eventCategory:id,color'])
            ->whereHas('event', function ($q) {
                $q->where('status', EventApprovalStatus::APPROVED);
            })->get(['id', 'event_id', 'time_start', 'time_end', 'date'])
            ->map(function ($recurrence) {
                return [
                    'title' => $recurrence->event->name . ' - ' . $recurrence->event->organization->code,
                    'start' => $recurrence->date->format('Y-m-d') . 'T' . $recurrence->time_start->format('H:i:s'),
                    'end' => $recurrence->date->format('Y-m-d') . 'T' . $recurrence->time_end->format('H:i:s'),
                    'color' => $recurrence->event->eventCategory->color
                ];
            });
    }

    public function render()
    {
        return view('livewire.pages.event.index', [
            'categories' => Category::all(['id', 'name']),
            'organizations' => Organization::all(['id', 'name']),
            'locations' => Location::all(['id', 'name', 'description']),
        ]);
    }
}
