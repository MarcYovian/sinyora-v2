<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Contact as ContactModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Contact extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ?ContactModel $selectedContact = null;
    public ?int $deleteId = null;

    #[Url(as: 'q')]
    public string $search = '';

    public string $statusFilter = '';
    public string $correlationId = '';

    public array $table_heads = ['#', 'Nama', 'Email', 'Telepon', 'Status', 'Tanggal', 'Aksi'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when status filter changes.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Set status filter.
     */
    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
        $this->selectedContact = null; // Clear selection when filter changes
    }

    /**
     * Select a contact to view details.
     */
    public function selectContact(int $id): void
    {
        try {
            $this->selectedContact = ContactModel::findOrFail($id);

            // Auto-update status to 'read' if currently 'new'
            if ($this->selectedContact->status === 'new') {
                $this->selectedContact->update(['status' => 'read']);

                Log::info('Contact status auto-updated to read', [
                    'contact_id' => $id,
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                ]);
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Contact not found for selection', ['contact_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Pesan tidak ditemukan.');
            $this->selectedContact = null;
        } catch (\Exception $e) {
            Log::error('Failed to load contact', [
                'contact_id' => $id,
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error('Gagal memuat pesan. Silakan coba lagi.');
            $this->selectedContact = null;
        }
    }

    /**
     * Update contact status.
     */
    public function updateStatus(int $id, string $status): void
    {
        try {
            $contact = ContactModel::findOrFail($id);

            if (in_array($status, ['new', 'read', 'replied'])) {
                $contact->update(['status' => $status]);

                Log::info('Contact status updated', [
                    'contact_id' => $id,
                    'new_status' => $status,
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                ]);

                flash()->success('Status berhasil diperbarui');
            } else {
                Log::warning('Invalid contact status provided', [
                    'contact_id' => $id,
                    'attempted_status' => $status,
                    'user_id' => Auth::id(),
                ]);
                flash()->error('Status tidak valid.');
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Contact not found for status update', ['contact_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Pesan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Failed to update contact status', [
                'contact_id' => $id,
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Gagal memperbarui status. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            ContactModel::findOrFail($id);
            $this->deleteId = $id;
            $this->dispatch('open-modal', 'delete-contact-confirmation');
        } catch (ModelNotFoundException $e) {
            Log::warning('Contact not found for delete confirmation', ['contact_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Pesan tidak ditemukan.');
        }
    }

    /**
     * Delete a contact message.
     */
    public function delete(): void
    {
        Log::info('Contact deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'contact_id' => $this->deleteId,
        ]);

        try {
            if ($this->deleteId) {
                $contact = ContactModel::findOrFail($this->deleteId);
                $contactName = $contact->name;
                $contact->delete();

                Log::info('Contact deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_contact_name' => $contactName,
                ]);

                flash()->success('Pesan berhasil dihapus');
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Contact not found for deletion', ['contact_id' => $this->deleteId, 'user_id' => Auth::id()]);
            flash()->error('Pesan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Contact deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'contact_id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Gagal menghapus pesan. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-contact-confirmation');
            $this->deleteId = null;
        }
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search', 'statusFilter');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $contacts = ContactModel::query()
            ->select(['id', 'name', 'email', 'phone', 'message', 'status', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('message', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.contact', [
            'table_heads' => $this->table_heads,
            'contacts' => $contacts,
        ]);
    }
}
