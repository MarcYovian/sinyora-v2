<?php

namespace App\Services;

use App\Enums\BorrowingStatus;
use App\Models\Activity;
use App\Models\Borrowing;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Rules\AssetAvailability;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingManagementService
{
    protected $eventRepository;
    protected $activityRepository;
    protected $borrowingRepository;
    /**
     * Create a new class instance.
     */
    public function __construct(
        BorrowingRepositoryInterface $borrowingRepository,
        EventRepositoryInterface $eventRepository,
        ActivityRepositoryInterface $activityRepository
    ) {
        $this->borrowingRepository = $borrowingRepository;
        $this->eventRepository = $eventRepository;
        $this->activityRepository = $activityRepository;
    }

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

                $user = User::find(Auth::id());

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
