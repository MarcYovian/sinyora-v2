<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Document\DocumentModal;
use App\Models\Document as ModelsDocument;
use App\Models\User as ModelsUser;
use App\Services\DocumentManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Document extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;
    #[Layout('layouts.app')]

    public ModelsDocument $document;
    public $attachment = null;

    #[Url(keep: true)]
    public string $search = '';

    #[Url(as: 'status', keep: true)]
    public string $filterStatus = '';


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

        try {
            $file = $this->attachment;
            $user = ModelsUser::find(Auth::id());

            app(DocumentManagementService::class)->storeNewDocument($file, $user);

            flash()->success('Dokumen berhasil diunggah.');
            $this->dispatch('close-modal', 'add-document-modal');
            $this->reset();
        } catch (\Exception $e) {
            Log::error('Gagal mengunggah dokumen: ' . $e->getMessage());
            flash()->error('Gagal mengunggah dokumen, silakan coba lagi.');
        }
    }

    public function confirmDelete(int $documentId)
    {
        $this->document = ModelsDocument::find($documentId);
        if (!$this->document) {
            flash()->error(__('Document not found.'));
            return;
        }

        if ($this->document->status === 'done') {
            flash()->error(__('You cannot delete a document that has been processed.'));
            return;
        }

        $this->dispatch('open-modal', 'delete-document-confirmation');
    }

    public function delete()
    {
        if (!$this->document) {
            flash()->error(__('Document not found.'));
            return;
        }

        try {
            app(DocumentManagementService::class)->deleteDocument($this->document);
            flash()->success(__('Document deleted successfully.'));
            $this->dispatch('close-modal', 'delete-document-confirmation');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus dokumen: ' . $e->getMessage());
            flash()->error('Gagal menghapus dokumen, silakan coba lagi.');
        }
    }

    #[On('refresh-documents')]
    public function render()
    {
        $table_heads = ['#', 'Subject', 'Submitter', 'Upload Date', 'Status', 'Processed By', 'Actions'];
        $search = $this->search;
        $filterStatus = $this->filterStatus;
        $documents = ModelsDocument::query()
            ->with(['submitter', 'processor'])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('subject', 'like', "%{$search}%")
                        ->orWhere('doc_num', 'like', "%{$search}%")
                        ->orWhere('original_file_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('submitter', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($filterStatus, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.document', [
            'documents' => $documents,
            'table_heads' => $table_heads,
        ]);
    }
}
