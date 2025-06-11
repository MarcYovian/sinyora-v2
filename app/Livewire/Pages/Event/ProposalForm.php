<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\GuestSubmitter;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProposalForm extends Component
{
    public function render()
    {
        return view('livewire.pages.event.proposal-form');
    }
}
