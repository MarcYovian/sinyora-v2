<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

                $data['borrower'] = $data['borrower'] ?? $user->name;
                $data['borrower_phone'] = $data['borrower_phone'] ?? $user->phone;

                return $this->borrowingRepository->create(creator: $user, event: $event, data: $data);
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }
}
