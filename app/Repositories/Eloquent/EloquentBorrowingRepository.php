<?php

namespace App\Repositories\Eloquent;

use App\Enums\BorrowingStatus;
use App\Models\Borrowing;
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
    public function create(array $data): Borrowing
    {
        $borrowing = Borrowing::create([
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'notes' => $data['notes'] ?? null,
            'status' => BorrowingStatus::PENDING,
            'borrower' => $data['borrower'] ?? null,
            'borrower_phone' => $data['borrower_phone'] ?? null,
            'creator_id' => $data['creator_id'],
            'creator_type' => $data['creator_type'],
            'borrowable_id' => $data['borrowable_id'],
            'borrowable_type' => $data['borrowable_type'],
        ]);

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

        $borrowing->assets()->detach();

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

        if (isset($data['assets'])) {
            $assetsToSync = [];
            foreach ($data['assets'] as $assetData) {
                // Pastikan formatnya benar sebelum sync
                $assetsToSync[$assetData['asset_id']] = ['quantity' => $assetData['quantity']];
            }
            // Lakukan sync pada relasi pivot table asset_borrowing
            $borrowing->assets()->sync($assetsToSync);
        }

        $borrowing->save();

        return $borrowing;
    }

    /**
     * @inheritDoc
     */
    public function updateStatus(int $id, BorrowingStatus $status): Borrowing
    {
        $borrowing = $this->findById($id);
        if (!$borrowing) {
            throw new \Exception("Borrowing with ID {$id} not found.");
        }
        $borrowing->status = $status;
        $borrowing->save();

        return $borrowing;
    }
}
