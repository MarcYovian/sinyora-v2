<?php

namespace App\Services;

use App\Enums\BorrowingStatus;
use App\Models\Activity;
use App\Models\Borrowing;
use App\Models\BorrowingDocument;
use App\Models\Document;
use App\Models\Event;
use App\Models\LicensingDocument;
use App\Models\User;
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

    public function createNewBorrowing(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                $event = null;

                if ($data['borrowable_type'] === 'activity') {
                    $event = $this->activityRepository->create([
                        'name' => $data['activity_name'],
                        'location' => $data['activity_location'],
                    ]);
                } elseif ($data['borrowable_type'] === 'event') {
                    $event = $this->eventRepository->findById($data['borrowable_id']);
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

                return $this->borrowingRepository->create(data: $preparedData);
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }

    public function createBorrowingFromDocument(Document $document, array $data)
    {
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
    }

    public function createBorrowingForEvent(
        Event|Activity|null $event,
        array $eventData,
        Carbon $start,
        Carbon $end,
        LicensingDocument|BorrowingDocument|null $documentable
    ) {
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

    public function updateBorrowing(Borrowing $borrowing, array $data)
    {
        return DB::transaction(function () use ($borrowing, $data) {
            try {
                $eventOrActivity = null;

                if ($data['borrowable_type'] === 'activity') {
                    // Cek apakah peminjaman sebelumnya sudah memiliki activity
                    // Jika ya, update activity yang ada. Jika tidak, buat baru.
                    if ($borrowing->borrowable_type === Activity::class) {
                        $eventOrActivity = $this->activityRepository->update($borrowing->borrowable_id, [
                            'name' => $data['activity_name'],
                            'location' => $data['activity_location'],
                        ]);
                    } else {
                        // Jika sebelumnya adalah event, dan sekarang ganti ke activity baru
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

                // Asosiasikan dengan event/activity yang baru/diupdate
                if ($eventOrActivity) {
                    $borrowing->event()->associate($eventOrActivity);
                }

                $updatedData = Arr::except($data, ['activity_name', 'activity_location', 'borrowable_type', 'borrowable_id']);

                return $this->borrowingRepository->update($borrowing->id, $updatedData);
            } catch (\Throwable $th) {
                // Rollback transaksi jika terjadi error
                throw $th;
            }
        });
    }

    public function deleteBorrowing(int $borrowingId)
    {
        return $this->borrowingRepository->delete($borrowingId);
    }

    public function approveBorrowing(int $borrowingId)
    {
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

        // if ($approvedBorrowing->creator->email) {
        //     Mail::to($approvedBorrowing->creator->email)->queue(new BorrowingApprovedMail($approvedBorrowing));
        // }

        return $approvedBorrowing;
    }

    public function rejectBorrowing(int $borrowingId)
    {
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

        // if ($rejectedBorrowing->creator->email) {
        //     Mail::to($rejectedBorrowing->creator->email)->queue(new BorrowingRejectedMail($rejectedBorrowing));
        // }

        return $rejectedBorrowing;
    }
}
