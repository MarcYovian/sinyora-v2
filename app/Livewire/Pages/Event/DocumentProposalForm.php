<?php

namespace App\Livewire\Pages\Event;

use App\Models\GuestSubmitter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
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
        $this->validate();

        DB::transaction(function () {
            // Simpan data submitter
            $guest  = GuestSubmitter::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'phone_number' => $this->phone,
                ]
            );

            // Simpan attachment
            if ($this->attachment) {
                $path = $this->attachment->store('documents/proposals', 'public');
                $mimeType = Storage::disk('public')->mimeType($path);
                $guest->documents()->create([
                    'document_path' => $path,
                    'original_file_name' => $this->attachment->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'status' => 'pending',
                ]);
                toastr()->success('Dokumen proposal berhasil diajukan.');
                $this->dispatch('close-modal', 'proposal-modal');
            } else {
                toastr()->error('Gagal mengunggah dokumen. Silakan coba lagi.');
            }
        });
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
