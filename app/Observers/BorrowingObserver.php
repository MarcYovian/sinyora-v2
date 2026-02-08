<?php

namespace App\Observers;

use App\Models\Borrowing;
use Illuminate\Support\Facades\Log;

class BorrowingObserver
{
    /**
     * Handle the Borrowing "created" event.
     */
    public function created(Borrowing $borrowing): void
    {
        Log::info('Borrowing created', [
            'id' => $borrowing->id,
            'borrower' => $borrowing->borrower,
            'start_datetime' => $borrowing->start_datetime,
            'end_datetime' => $borrowing->end_datetime,
            'status' => $borrowing->status->value ?? $borrowing->status,
            'assets_count' => $borrowing->assets()->count(),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Borrowing "updated" event.
     */
    public function updated(Borrowing $borrowing): void
    {
        Log::info('Borrowing updated', [
            'id' => $borrowing->id,
            'borrower' => $borrowing->borrower,
            'status' => $borrowing->status->value ?? $borrowing->status,
            'changes' => $borrowing->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Borrowing "deleted" event.
     */
    public function deleted(Borrowing $borrowing): void
    {
        Log::info('Borrowing deleted', [
            'id' => $borrowing->id,
            'borrower' => $borrowing->borrower,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Borrowing "restored" event.
     */
    public function restored(Borrowing $borrowing): void
    {
        Log::info('Borrowing restored', [
            'id' => $borrowing->id,
            'borrower' => $borrowing->borrower,
        ]);
    }

    /**
     * Handle the Borrowing "force deleted" event.
     */
    public function forceDeleted(Borrowing $borrowing): void
    {
        Log::info('Borrowing force deleted', [
            'id' => $borrowing->id,
            'borrower' => $borrowing->borrower,
        ]);
    }
}
