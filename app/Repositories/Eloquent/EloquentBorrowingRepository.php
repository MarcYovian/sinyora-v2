<?php

namespace App\Repositories\Eloquent;

use App\Enums\BorrowingStatus;
use App\Models\Borrowing;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        Log::debug('Creating borrowing in repository', [
            'start_datetime' => $data['start_datetime'] ?? null,
            'end_datetime' => $data['end_datetime'] ?? null,
            'assets_count' => count($data['assets'] ?? []),
        ]);

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
                $assetsToSync[$assetData['asset_id']] = ['quantity' => $assetData['quantity']];
            }
        }

        $borrowing->assets()->sync($assetsToSync);

        Log::debug('Borrowing created in repository', [
            'borrowing_id' => $borrowing->id,
            'assets_synced' => count($assetsToSync),
        ]);

        return $borrowing;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $borrowing = $this->findById($id);
        if (!$borrowing) {
            Log::warning('Attempted to delete non-existent borrowing', ['borrowing_id' => $id]);
            return false;
        }

        $borrowing->assets()->detach();

        $result = $borrowing->delete();

        Log::debug('Borrowing deleted in repository', [
            'borrowing_id' => $id,
            'success' => $result,
        ]);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Borrowing
    {
        return Borrowing::find($id);
    }

    /**
     * Find by ID or throw exception.
     */
    public function findByIdOrFail(int $id): Borrowing
    {
        $borrowing = Borrowing::find($id);

        if (!$borrowing) {
            throw new ModelNotFoundException("Borrowing with ID {$id} not found.");
        }

        return $borrowing;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Borrowing
    {
        $borrowing = $this->findById($id);
        if (!$borrowing) {
            throw new ModelNotFoundException("Borrowing with ID {$id} not found.");
        }

        Log::debug('Updating borrowing in repository', [
            'borrowing_id' => $id,
            'fields_updated' => array_keys($data),
        ]);

        $borrowing->fill($data);

        if (isset($data['assets'])) {
            $assetsToSync = [];
            foreach ($data['assets'] as $assetData) {
                $assetsToSync[$assetData['asset_id']] = ['quantity' => $assetData['quantity']];
            }
            $borrowing->assets()->sync($assetsToSync);

            Log::debug('Assets synced for borrowing', [
                'borrowing_id' => $id,
                'assets_count' => count($assetsToSync),
            ]);
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
            throw new ModelNotFoundException("Borrowing with ID {$id} not found.");
        }

        $oldStatus = $borrowing->status;
        $borrowing->status = $status;
        $borrowing->save();

        Log::debug('Borrowing status updated in repository', [
            'borrowing_id' => $id,
            'old_status' => $oldStatus->value ?? $oldStatus,
            'new_status' => $status->value,
        ]);

        return $borrowing;
    }
}
