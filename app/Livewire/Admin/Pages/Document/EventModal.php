<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Livewire\Forms\DocumentForm;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class EventModal extends Component
{
    public ?DocumentForm $form;
    public array $data = [];

    #[On('setDataForEvent')]
    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function save()
    {
        $data = $this->data;
        // dd($data);

        $this->form->setData($data);
        $this->form->storeDocument();

        $this->dispatch('close-modal', 'event-modal');
    }

    public function render()
    {
        return view('livewire.admin.pages.document.event-modal');
    }
}
