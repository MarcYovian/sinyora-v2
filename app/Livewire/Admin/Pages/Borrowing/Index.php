<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Borrowing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public BorrowingForm $form;

    public $borrowing = [];

    public $approveId;

    public $rejectId;
    public $deleteId;

    public function show(Borrowing $borrowing)
    {
        $this->authorize('access', 'admin.asset-borrowings.show');

        $this->reset('borrowing');
        $this->borrowing = $borrowing->load(['assets', 'user', 'event']);
        // dd($this->borrowing->toArray());

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
        $this->authorize('access', 'admin.asset-borrowings.delete');

        // dd($borrowing);
        $this->deleteId = $borrowing->id;
        $this->borrowing = $borrowing;
        $this->dispatch('open-modal', 'delete-borrowing-confirmation');
    }

    public function approve()
    {
        $this->authorize('access', 'admin.asset-borrowings.approve');

        if ($this->approveId) {
            $this->form->approve($this->approveId);

            if ($this->getErrorBag()->isNotEmpty()) {
                toastr()->error($this->getErrorBag()->first());
                return;
            }

            toastr()->success('Borrowing approved successfully');
        }
        $this->dispatch('close-modal', 'approve-borrowing-confirmation');
        $this->dispatch('close-modal', 'borrowing-detail-modal');
    }

    public function reject()
    {
        $this->authorize('access', 'admin.asset-borrowings.reject');

        if ($this->rejectId) {
            Borrowing::find($this->rejectId)->update(['status' => BorrowingStatus::REJECTED]);
            $this->rejectId = null;
            toastr()->success('Borrowing rejected successfully');
        }
        $this->dispatch('close-modal', 'reject-borrowing-confirmation');
        $this->dispatch('close-modal', 'borrowing-detail-modal');
    }

    public function destroy()
    {
        $this->authorize('access', 'admin.asset-borrowings.destroy');

        if ($this->deleteId) {
            $this->form->setBorrowing(Borrowing::find($this->deleteId));
            $this->form->destroy();
            $this->deleteId = null;
            $this->borrowing = [];
            toastr()->success('Borrowing deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-borrowing-confirmation');
    }

    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.index');

        $table_heads = ['#', 'Borrower', 'Event', 'date', 'Note', 'Status', 'Actions'];

        $borrowings = Borrowing::with(['assets', 'user', 'event'])->latest()->paginate(5);
        // dd($borrowings->toArray());
        return view('livewire.admin.pages.borrowing.index', [
            'table_heads' => $table_heads,
            'borrowings' => $borrowings
        ]);
    }
}
