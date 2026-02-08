<?php

namespace App\Services;

use App\Enums\BorrowingStatus;
use App\Models\Activity;
use App\Models\Borrowing;
use App\Models\BorrowingDocument;
use App\Models\Document;
use App\Models\Event;
use App\Models\LicensingDocument;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\BorrowingDocumentRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Rules\AssetAvailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BorrowingManagementService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected BorrowingRepositoryInterface $borrowingRepository,
        protected EventRepositoryInterface $eventRepository,
        protected ActivityRepositoryInterface $activityRepository,
        protected UserRepositoryInterface $userRepository,
        protected BorrowingDocumentRepositoryInterface $borrowingDocumentRepository
    ) {}

    /**
     * Create a new borrowing.
     */
    public function createNewBorrowing(array $data): Borrowing
    {
        Log::info('Creating new borrowing', [
            'user_id' => Auth::id(),
            'borrowable_type' => $data['borrowable_type'] ?? null,
            'assets_count' => count($data['assets'] ?? []),
        ]);

        return DB::transaction(function () use ($data) {
            try {
                $event = null;

                if ($data['borrowable_type'] === 'activity') {
                    $event = $this->activityRepository->create([
                        'name' => $data['activity_name'],
                        'location' => $data['activity_location'],
                    ]);

                    Log::debug('Activity created for borrowing', ['activity_id' => $event->id]);
                } elseif ($data['borrowable_type'] === 'event') {
                    $event = $this->eventRepository->findById($data['borrowable_id']);
                    
                    Log::debug('Using existing event for borrowing', ['event_id' => $event->id]);
                } else {
                    throw new \InvalidArgumentException('Invalid borrowable type provided.');
                }

                $user = $this->userRepository->findById(Auth::id());

                $preparedData = array_merge($data, [
                    'creator_id' => $user->id,
                    'creator_type' => $user->getMorphClass(),
                    'borrowable_id' => $event->id,
                    'borrowable_type' => $event->getMorphClass(),
                    'borrower' => $data['borrower'] ?? $user->name,
                    'borrower_phone' => $data['borrower_phone'] ?? $user->phone,
                ]);

                $borrowing = $this->borrowingRepository->create(data: $preparedData);

                Log::info('Borrowing created successfully', [
                    'borrowing_id' => $borrowing->id,
                    'user_id' => Auth::id(),
                ]);

                return $borrowing;
            } catch (\Throwable $th) {
                Log::error('Failed to create borrowing', [
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Create borrowing from document.
     */
    public function createBorrowingFromDocument(Document $document, array $data): void
    {
        Log::info('Creating borrowing from document', [
            'document_id' => $document->id,
            'dates_count' => count(data_get($data, 'parsed_dates.dates', [])),
        ]);

        foreach (data_get($data, 'parsed_dates.dates', []) as $date) {
            $startDateTime = Carbon::parse($date['start']);
            $endDateTime = Carbon::parse($date['end']);

            $activity = $this->activityRepository->create([
                'name' => $data['activity_name'],
                'location' => $data['activity_location'],
            ]);

            $borrowingDocument = $this->borrowingDocumentRepository->create([
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
            ]);

            $this->createBorrowingForEvent(
                event: $activity,
                eventData: $data,
                start: $startDateTime,
                end: $endDateTime,
                documentable: $borrowingDocument
            );

            $document->borrowingDocuments()->attach($borrowingDocument->id);
        }

        Log::info('Borrowings created from document', ['document_id' => $document->id]);
    }

    /**
     * Create borrowing for event.
     */
    public function createBorrowingForEvent(
        Event|Activity|null $event,
        array $eventData,
        Carbon $start,
        Carbon $end,
        LicensingDocument|BorrowingDocument|null $documentable
    ): Borrowing {
        $user = $this->userRepository->findById(Auth::id());

        $borrowingData = [
            'start_datetime' => $start,
            'end_datetime' => $end,
            'borrower' => $eventData['organizer'][0]['name'] ?? $user->name,
            'borrower_phone' => $eventData['organizer'][0]['contact'] ?? $user->phone,
            'status' => BorrowingStatus::PENDING,
            'creator_id' => $user->id,
            'creator_type' => $user->getMorphClass(),
            'borrowable_id' => $event ? $event->id : null,
            'borrowable_type' => $event ? $event->getMorphClass() : null,
            'document_typable_id' => $documentable ? $documentable->id : null,
            'document_typable_type' => $documentable ? $documentable->getMorphClass() : null,
            'assets' => collect($eventData['equipment'])->map(function ($equipment) {
                return [
                    'asset_id' => $equipment['item_id'],
                    'quantity' => $equipment['quantity'],
                ];
            })->toArray(),
        ];

        return $this->borrowingRepository->create(data: $borrowingData);
    }

    /**
     * Update an existing borrowing.
     */
    public function updateBorrowing(Borrowing $borrowing, array $data): Borrowing
    {
        Log::info('Updating borrowing', [
            'borrowing_id' => $borrowing->id,
            'user_id' => Auth::id(),
        ]);

        return DB::transaction(function () use ($borrowing, $data) {
            try {
                $eventOrActivity = null;

                if ($data['borrowable_type'] === 'activity') {
                    // Check if previous borrowing already has an activity
                    if ($borrowing->borrowable_type === Activity::class) {
                        $eventOrActivity = $this->activityRepository->update($borrowing->borrowable_id, [
                            'name' => $data['activity_name'],
                            'location' => $data['activity_location'],
                        ]);
                    } else {
                        // If previously was event, create new activity
                        $eventOrActivity = $this->activityRepository->create([
                            'name' => $data['activity_name'],
                            'location' => $data['activity_location'],
                        ]);
                    }
                } elseif ($data['borrowable_type'] === 'event') {
                    $eventOrActivity = $this->eventRepository->findById($data['borrowable_id']);
                } else {
                    throw new \InvalidArgumentException('Invalid borrowable type provided.');
                }

                // Associate with new/updated event/activity
                if ($eventOrActivity) {
                    $borrowing->event()->associate($eventOrActivity);
                }

                $updatedData = Arr::except($data, ['activity_name', 'activity_location', 'borrowable_type', 'borrowable_id']);

                $updatedBorrowing = $this->borrowingRepository->update($borrowing->id, $updatedData);

                Log::info('Borrowing updated successfully', [
                    'borrowing_id' => $borrowing->id,
                    'user_id' => Auth::id(),
                ]);

                return $updatedBorrowing;
            } catch (\Throwable $th) {
                Log::error('Failed to update borrowing', [
                    'borrowing_id' => $borrowing->id,
                    'user_id' => Auth::id(),
                    'error' => $th->getMessage(),
                ]);
                throw $th;
            }
        });
    }

    /**
     * Delete a borrowing.
     */
    public function deleteBorrowing(int $borrowingId): bool
    {
        Log::info('Deleting borrowing', [
            'borrowing_id' => $borrowingId,
            'user_id' => Auth::id(),
        ]);

        $result = $this->borrowingRepository->delete($borrowingId);

        if ($result) {
            Log::info('Borrowing deleted successfully', ['borrowing_id' => $borrowingId]);
        } else {
            Log::warning('Failed to delete borrowing', ['borrowing_id' => $borrowingId]);
        }

        return $result;
    }

    /**
     * Approve a borrowing.
     */
    public function approveBorrowing(int $borrowingId): Borrowing
    {
        Log::info('Approving borrowing', [
            'borrowing_id' => $borrowingId,
            'user_id' => Auth::id(),
        ]);

        $borrowing = $this->borrowingRepository->findById($borrowingId);

        if (!$borrowing) {
            throw new ModelNotFoundException('Peminjaman tidak ditemukan.');
        }

        if ($borrowing->status !== BorrowingStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Hanya peminjaman dengan status PENDING yang bisa disetujui.',
            ]);
        }

        foreach ($borrowing->assets as $asset) {
            if (!$asset->is_active) {
                throw ValidationException::withMessages(['asset' => "Aset {$asset->name} sedang tidak aktif."]);
            }

            (new AssetAvailability(
                assetId: $asset->id,
                startDate: $borrowing->start_datetime,
                endDate: $borrowing->end_datetime,
                excludeBorrowingId: $borrowing->id
            ))->validate('quantity', $asset->pivot->quantity, fn($message) => throw ValidationException::withMessages(['asset' => $message]));
        }

        $approvedBorrowing = $this->borrowingRepository->updateStatus($borrowingId, BorrowingStatus::APPROVED);

        Log::info('Borrowing approved successfully', [
            'borrowing_id' => $borrowingId,
            'approved_by' => Auth::id(),
        ]);

        // if ($approvedBorrowing->creator->email) {
        //     Mail::to($approvedBorrowing->creator->email)->queue(new BorrowingApprovedMail($approvedBorrowing));
        // }

        return $approvedBorrowing;
    }

    /**
     * Reject a borrowing.
     */
    public function rejectBorrowing(int $borrowingId): Borrowing
    {
        Log::info('Rejecting borrowing', [
            'borrowing_id' => $borrowingId,
            'user_id' => Auth::id(),
        ]);

        $borrowing = $this->borrowingRepository->findById($borrowingId);

        if (!$borrowing) {
            throw new ModelNotFoundException('Peminjaman tidak ditemukan.');
        }

        if ($borrowing->status !== BorrowingStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Hanya peminjaman dengan status PENDING yang bisa ditolak.',
            ]);
        }

        $rejectedBorrowing = $this->borrowingRepository->updateStatus($borrowingId, BorrowingStatus::REJECTED);

        Log::info('Borrowing rejected successfully', [
            'borrowing_id' => $borrowingId,
            'rejected_by' => Auth::id(),
        ]);

        // if ($rejectedBorrowing->creator->email) {
        //     Mail::to($rejectedBorrowing->creator->email)->queue(new BorrowingRejectedMail($rejectedBorrowing));
        // }

        return $rejectedBorrowing;
    }
}
