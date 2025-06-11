<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\GuestSubmitter;
use App\Models\Location;
use App\Models\Organization;
use Livewire\Component;

class ManualProposalForm extends Component
{
    public EventProposalForm $form;

    public function save()
    {
        $this->form->store();
        $this->dispatch('close-modal', 'proposal-modal');
    }

    public function render()
    {
        return view('livewire.pages.event.manual-proposal-form', [
            'categories' => Category::all(['id', 'name']),
            'organizations' => Organization::all(['id', 'name']),
            'locations' => Location::all(['id', 'name', 'description']),
        ]);
    }
}
