<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Document\DocumentModal;
use App\Models\Document as ModelsDocument;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Document extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;
    #[Layout('layouts.app')]

    public $attachment = null;

    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'mimes:pdf,png,jpg', 'max:5120'], // Maks 5MB
        ];
    }

    public function viewDetails($id)
    {
        $this->dispatch('setDataForDetailDocument', documentId: $id)->to(DocumentModal::class);
        $this->dispatch('open-modal', 'document-modal');
    }

    public function add()
    {
        $this->attachment = null;
        $this->dispatch('reset-file-input');
        $this->dispatch('open-modal', 'add-document-modal');
    }

    public function removeAttachment()
    {
        if ($this->attachment) {
            $this->attachment = null;
        }
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            // Simpan data submitter
            $user = User::find(Auth::id());

            // Simpan attachment
            if ($this->attachment) {
                $path = $this->attachment->store('documents/proposals', 'public');
                $mimeType = Storage::disk('public')->mimeType($path);
                $user->documents()->create([
                    'document_path' => $path,
                    'original_file_name' => $this->attachment->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'status' => 'pending',
                ]);
            }

            $this->dispatch('close-modal', 'add-document-modal');
        });
    }

    public function render()
    {
        $table_heads = ['#', 'Submitters', 'Filename', 'Mime Type', 'Status', 'Actions'];

        $documents = ModelsDocument::with(['submitter'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.pages.document', [
            'documents' => $documents,
            'table_heads' => $table_heads,
        ]);
    }
}
