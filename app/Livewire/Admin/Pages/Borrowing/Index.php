<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Borrowing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public BorrowingForm $form;

    public $borrowing;

    public $approveId;
    public $rejectId;
    public $deleteId;

    #[Url(keep: true)]
    public string $search = '';

    #[Url(as: 'status', keep: true)]
    public string $filterStatus = '';

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'filterStatus');
        $this->resetPage();
    }

    public function show(Borrowing $borrowing)
    {
        $this->authorize('access', 'admin.asset-borrowings.show');
        $this->reset('borrowing');
        $this->borrowing = $borrowing->load(['assets', 'creator', 'event']);
        $this->dispatch('open-modal', 'borrowing-detail-modal');
    }

    public function confirmApprove($id)
    {
        $this->authorize('access', 'admin.asset-borrowings.approve');
        $this->approveId = $id;
        $this->dispatch('open-modal', 'approve-borrowing-confirmation');
    }

    public function confirmReject($id)
    {
        $this->authorize('access', 'admin.asset-borrowings.reject');
        $this->rejectId = $id;
        $this->dispatch('open-modal', 'reject-borrowing-confirmation');
    }

    public function confirmDelete(Borrowing $borrowing)
    {
        $this->authorize('access', 'admin.asset-borrowings.destroy');
        $this->deleteId = $borrowing->id;
        $this->dispatch('open-modal', 'delete-borrowing-confirmation');
    }

    public function approve()
    {
        $this->authorize('access', 'admin.asset-borrowings.approve');
        if (!$this->approveId) {
            return;
        }
        try {
            $this->form->approve($this->approveId);

            // 2. Baris ini HANYA akan berjalan jika tidak ada exception (proses sukses)
            flash()->success('Peminjaman berhasil disetujui.');
            $this->approveId = null;
            $this->dispatch('close-modal', 'approve-borrowing-confirmation');
            $this->dispatch('close-modal', 'borrowing-detail-modal');
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            // 4. Tangkap error umum lainnya
            flash()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Approval Exception in Component: ' . $e->getMessage());
        }
    }

    public function reject()
    {
        $this->authorize('access', 'admin.asset-borrowings.reject');
        if (!$this->rejectId) {
            return;
        }

        try {
            $this->form->reject($this->rejectId);
            flash()->success('Peminjaman berhasil ditolak.');
            $this->dispatch('close-modal', 'reject-borrowing-confirmation');
            $this->dispatch('close-modal', 'borrowing-detail-modal');
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            flash()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Rejection Exception in Component: ' . $e->getMessage());
        } finally {
            $this->rejectId = null;
        }
    }

    public function destroy()
    {
        $this->authorize('access', 'admin.asset-borrowings.destroy');

        if (!$this->deleteId) {
            return;
        }

        try {
            $this->form->destroy($this->deleteId);
            flash()->success('Peminjaman berhasil dihapus.');
            $this->dispatch('close-modal', 'delete-borrowing-confirmation');
        } catch (\Exception $e) {
            flash()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Deletion Exception in Component: ' . $e->getMessage());
        } finally {
            $this->deleteId = null;
        }
    }

    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.index');

        $table_heads = ['#', 'Peminjam', 'Periode', 'Aktivitas Terkait', 'Status', ''];

        $borrowings = Borrowing::query()
            ->with(['creator', 'event'])
            ->when($this->search, function ($query) {
                $query->where('borrower', 'like', '%' . $this->search . '%')
                    ->orWhereHas('event', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.borrowing.index', [
            'table_heads' => $table_heads,
            'borrowings' => $borrowings
        ]);
    }
}
