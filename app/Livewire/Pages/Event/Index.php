<?php

namespace App\Livewire\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\EventRecurrence;
use App\Models\GuestSubmitter;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;
    #[Layout('components.layouts.app')]
    #[Title('Kalender Kegiatan')]

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

    public function render()
    {
        return view('livewire.pages.event.index', [
            'categories' => Category::all(['id', 'name']),
            'organizations' => Organization::all(['id', 'name']),
            'locations' => Location::all(['id', 'name', 'description']),
        ]);
    }
}
