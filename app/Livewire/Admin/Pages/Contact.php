<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Contact as ContactModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Contact extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ?ContactModel $selectedContact = null;
    public ?int $deleteId = null;
    public string $search = '';
    public string $statusFilter = '';

    public function show(int $id)
    {
        $this->selectedContact = ContactModel::findOrFail($id);

        // Auto-update status to 'read' if currently 'new'
        if ($this->selectedContact->status === 'new') {
            $this->selectedContact->update(['status' => 'read']);
        }

        $this->dispatch('open-modal', 'view-contact-modal');
    }

    public function updateStatus(int $id, string $status)
    {
        $contact = ContactModel::findOrFail($id);

        if (in_array($status, ['new', 'read', 'replied'])) {
            $contact->update(['status' => $status]);
            flash()->success('Status berhasil diperbarui');
        }
    }

    public function confirmDelete(int $id)
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal', 'delete-contact-confirmation');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $contact = ContactModel::findOrFail($this->deleteId);
            $contact->delete();
            flash()->success('Pesan berhasil dihapus');
        }

        $this->dispatch('close-modal', 'delete-contact-confirmation');
        $this->deleteId = null;
    }

    public function render()
    {
        $table_heads = ['#', 'Nama', 'Email', 'Telepon', 'Status', 'Tanggal', 'Aksi'];

        $contacts = ContactModel::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('message', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.contact', [
            'table_heads' => $table_heads,
            'contacts' => $contacts,
        ]);
    }
}
