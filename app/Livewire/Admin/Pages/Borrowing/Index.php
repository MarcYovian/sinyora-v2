<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Borrowing;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    public ?int $approveId = null;
    public ?int $rejectId = null;
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
    }

    /**
     * Handle property updates.
     */
    public function updated(string $propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus'])) {
            $this->resetPage();
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
     * Show borrowing detail modal.
     */
    public function show(Borrowing $borrowing): void
    {
        $this->authorize('access', 'admin.asset-borrowings.show');
        $this->reset('borrowing');
        $this->borrowing = $borrowing->load(['assets', 'creator', 'event']);
        $this->dispatch('open-modal', 'borrowing-detail-modal');

        Log::debug('Borrowing detail viewed', [
            'borrowing_id' => $borrowing->id,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Open approve confirmation modal.
     */
    public function confirmApprove(Borrowing $borrowing): void
    {
        $this->authorize('access', 'admin.asset-borrowings.approve');
        $this->approveId = $borrowing->id;
        $this->borrowing = $borrowing;
        $this->dispatch('open-modal', 'approve-borrowing-confirmation');
    }

    /**
     * Open reject confirmation modal.
     */
    public function confirmReject(Borrowing $borrowing): void
    {
        $this->authorize('access', 'admin.asset-borrowings.reject');
        $this->rejectId = $borrowing->id;
        $this->borrowing = $borrowing;
        $this->dispatch('open-modal', 'reject-borrowing-confirmation');
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(Borrowing $borrowing): void
    {
        $this->authorize('access', 'admin.asset-borrowings.destroy');
        $this->deleteId = $borrowing->id;
        $this->borrowing = $borrowing;
        $this->dispatch('open-modal', 'delete-borrowing-confirmation');
    }

    /**
     * Approve a borrowing request.
     */
    public function approve(): void
    {
        Log::info('Borrowing approval initiated', [
            'borrowing_id' => $this->approveId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.asset-borrowings.approve');

            if (!$this->approveId) {
                return;
            }

            $this->form->approve($this->approveId);

            flash()->success('Peminjaman berhasil disetujui.');
            $this->dispatch('close-modal', 'approve-borrowing-confirmation');
            $this->dispatch('close-modal', 'borrowing-detail-modal');

            Log::info('Borrowing approved successfully', [
                'borrowing_id' => $this->approveId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menyetujui peminjaman.');
            Log::warning('Unauthorized approval attempt', [
                'borrowing_id' => $this->approveId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
            Log::warning('Approval validation failed', [
                'borrowing_id' => $this->approveId,
                'error' => $e->validator->errors()->first(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Borrowing approval failed', [
                'borrowing_id' => $this->approveId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->approveId = null;
        }
    }

    /**
     * Reject a borrowing request.
     */
    public function reject(): void
    {
        Log::info('Borrowing rejection initiated', [
            'borrowing_id' => $this->rejectId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.asset-borrowings.reject');

            if (!$this->rejectId) {
                return;
            }

            $this->form->reject($this->rejectId);

            flash()->success('Peminjaman berhasil ditolak.');
            $this->dispatch('close-modal', 'reject-borrowing-confirmation');
            $this->dispatch('close-modal', 'borrowing-detail-modal');

            Log::info('Borrowing rejected successfully', [
                'borrowing_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menolak peminjaman.');
            Log::warning('Unauthorized rejection attempt', [
                'borrowing_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
            Log::warning('Rejection validation failed', [
                'borrowing_id' => $this->rejectId,
                'error' => $e->validator->errors()->first(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Borrowing rejection failed', [
                'borrowing_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->rejectId = null;
        }
    }

    /**
     * Delete a borrowing record.
     */
    public function destroy(): void
    {
        Log::info('Borrowing deletion initiated', [
            'borrowing_id' => $this->deleteId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.asset-borrowings.destroy');

            if (!$this->deleteId) {
                return;
            }

            $this->form->destroy($this->deleteId);

            flash()->success('Peminjaman berhasil dihapus.');
            $this->dispatch('close-modal', 'delete-borrowing-confirmation');

            Log::info('Borrowing deleted successfully', [
                'borrowing_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menghapus peminjaman.');
            Log::warning('Unauthorized deletion attempt', [
                'borrowing_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Borrowing deletion failed', [
                'borrowing_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->deleteId = null;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.index');

        $table_heads = ['#', 'Peminjam', 'Periode', 'Aktivitas Terkait', 'Status', ''];

        $borrowings = Borrowing::query()
            ->select([
                'id',
                'borrower',
                'borrower_phone',
                'start_datetime',
                'end_datetime',
                'status',
                'creator_id',
                'creator_type',
                'borrowable_id',
                'borrowable_type',
                'created_at',
            ])
            ->with([
                'creator:id,name,email',
                'event:id,name',
            ])
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
