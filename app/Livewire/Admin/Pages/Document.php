<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\DocumentStatus;
use App\Livewire\Admin\Pages\Document\DocumentModal;
use App\Models\Document as ModelsDocument;
use App\Services\DocumentManagementService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public ?ModelsDocument $document = null;
    public $attachment = null;
    public ?int $deleteId = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 's')]
    public string $filterStatus = '';

    public string $correlationId = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
        $this->authorize('access', 'admin.documents.index');
    }

    /**
     * Validation rules for file upload.
     */
    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'mimes:pdf,png,jpg', 'max:5120'], // Maks 5MB
        ];
    }

    /**
     * Handle property updates (reset pagination on filter change).
     */
    public function updated(string $propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    /**
     * View document details.
     */
    public function viewDetails(int $id): void
    {
        $this->authorize('access', 'admin.documents.show');

        $this->dispatch('setDataForDetailDocument', documentId: $id)->to(DocumentModal::class);
        $this->dispatch('open-modal', 'document-modal');

        Log::debug('Document detail modal opened', [
            'document_id' => $id,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Open modal for adding a new document.
     */
    public function add(): void
    {
        $this->authorize('access', 'admin.documents.create');

        $this->attachment = null;
        $this->dispatch('reset-file-input');
        $this->dispatch('open-modal', 'add-document-modal');

        Log::debug('Add document modal opened', [
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Remove the current attachment.
     */
    public function removeAttachment(): void
    {
        if ($this->attachment) {
            $this->attachment = null;
        }
    }

    /**
     * Save newly uploaded document.
     */
    public function save(): void
    {
        try {
            $this->authorize('access', 'admin.documents.create');
            $this->validate();

            $file = $this->attachment;
            $user = Auth::user();

            Log::info('Storing new document', [
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'correlation_id' => $this->correlationId,
            ]);

            app(DocumentManagementService::class)->storeNewDocument($file, $user);

            flash()->success('Dokumen berhasil diunggah.');
            $this->dispatch('close-modal', 'add-document-modal');
            $this->reset(['attachment']);

            Log::info('Document stored successfully', [
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk operasi ini.');
            Log::warning('Unauthorized document upload attempt', [
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Document upload failed', [
                'user_id' => auth()->id(),
                'file_name' => $this->attachment?->getClientOriginalName(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $documentId): void
    {
        $this->authorize('access', 'admin.documents.delete');

        $this->document = ModelsDocument::select(['id', 'subject', 'original_file_name', 'status', 'document_path'])
            ->find($documentId);

        if (!$this->document) {
            flash()->error(__('Dokumen tidak ditemukan.'));
            return;
        }

        if ($this->document->status === DocumentStatus::DONE) {
            flash()->error(__('Dokumen yang sudah selesai diproses tidak dapat dihapus.'));
            return;
        }

        $this->deleteId = $documentId;
        $this->dispatch('open-modal', 'delete-document-confirmation');
    }

    /**
     * Delete a document.
     */
    public function delete(): void
    {
        Log::info('Document deletion initiated', [
            'document_id' => $this->deleteId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.documents.delete');

            if (!$this->deleteId || !$this->document) {
                return;
            }

            app(DocumentManagementService::class)->deleteDocument($this->document);

            flash()->success(__('Dokumen berhasil dihapus.'));
            $this->dispatch('close-modal', 'delete-document-confirmation');

            Log::info('Document deleted successfully', [
                'document_id' => $this->deleteId,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menghapus dokumen.');
            Log::warning('Unauthorized document deletion attempt', [
                'document_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Document deletion failed', [
                'document_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->deleteId = null;
        }
    }

    /**
     * Reset all filters.
     */
    public function resetFilters(): void
    {
        $this->reset('search', 'filterStatus');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[On('refresh-documents')]
    public function render()
    {
        $table_heads = ['No', 'Dokumen', 'Pengaju', 'Tanggal Unggah', 'Status', 'Diproses Oleh', 'Aksi'];

        $documents = ModelsDocument::query()
            ->select([
                'id', 'subject', 'doc_num', 'original_file_name',
                'status', 'submitter_type', 'submitter_id',
                'processed_by', 'processed_at', 'created_at',
                'document_path',
            ])
            ->with(['submitter', 'processor:id,name'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                        ->orWhere('doc_num', 'like', '%' . $this->search . '%')
                        ->orWhere('original_file_name', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('submitter', function ($subQuery) {
                        $subQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.document', [
            'documents' => $documents,
            'table_heads' => $table_heads,
        ]);
    }
}
