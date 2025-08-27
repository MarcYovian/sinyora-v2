<?php

namespace App\Livewire\Pages\Event;

use App\Models\GuestSubmitter;
use App\Services\DocumentManagementService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentProposalForm extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $attachment = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'attachment' => ['required', 'file', 'mimes:pdf,png,jpg', 'max:5120'], // Maks 5MB
        ];
    }

    public function save()
    {
        try {
            $this->validate();

            $guest  = GuestSubmitter::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'phone_number' => $this->phone,
                ]
            );

            app(DocumentManagementService::class)->storeNewDocument($this->attachment, $guest);

            toastr()->success('Dokumen proposal berhasil diajukan.');
            $this->dispatch('close-modal', 'proposal-modal');

            $this->reset();
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
        }
    }

    public function removeDocument()
    {
        if ($this->attachment) {
            $this->attachment->delete();
            $this->attachment = null;
        }
    }

    public function render()
    {
        return view('livewire.pages.event.document-proposal-form');
    }
}
