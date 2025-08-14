<?php

namespace App\Repositories\Eloquent;

use App\Enums\BorrowingStatus;
use App\Models\Borrowing;
use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Models\User;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentBorrowingRepository implements BorrowingRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return Borrowing::all();
    }

    /**
     * @inheritDoc
     */
    public function create(User|GuestSubmitter $creator, ?Event $event, array $data): Borrowing
    {
        if ($creator instanceof User) {
            $borrowerName = $creator->name;
            $borrowerPhone = $creator->phone;
        } else {
            $borrowerName = $data['guestName'];
            $borrowerPhone = $data['guestPhone'];
        }

        $borrowing = new Borrowing([
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'notes' => $data['notes'] ?? null,
            'status' => BorrowingStatus::PENDING,
            'borrower' => $borrowerName,
            'borrower_phone' => $borrowerPhone,
        ]);

        $borrowing->creator()->associate($creator);

        if ($event) {
            $borrowing->event()->associate($event);
        }

        $borrowing->save();

        $assetsToSync = [];
        if (!empty($data['assets'])) {
            foreach ($data['assets'] as $assetData) {
                // Pastikan formatnya benar sebelum sync
                $assetsToSync[$assetData['asset_id']] = ['quantity' => $assetData['quantity']];
            }
        }

        $borrowing->assets()->sync($assetsToSync);

        return $borrowing;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $borrowing = $this->findById($id);
        if (!$borrowing) {
            return false;
        }

        return $borrowing->delete();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): Borrowing|null
    {
        return Borrowing::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Borrowing
    {
        $borrowing = $this->findById($id);
        if (!$borrowing) {
            throw new \Exception("Borrowing with ID {$id} not found.");
        }

        // Update the borrowing with the provided data
        $borrowing->fill($data);
        $borrowing->save();

        return $borrowing;
    }
}
