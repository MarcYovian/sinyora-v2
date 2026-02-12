<?php

namespace Tests\Unit\Services;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\BorrowingDocumentRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\BorrowingManagementService;
use PHPUnit\Framework\TestCase;
use Mockery\MockInterface;

class BorrowingManagementServiceTest extends TestCase
{
    protected BorrowingManagementService $service;
    protected MockInterface $borrowingRepository;
    protected MockInterface $eventRepository;
    protected MockInterface $activityRepository;
    protected MockInterface $userRepository;
    protected MockInterface $borrowingDocumentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Log facade since service uses logging throughout
        \Illuminate\Support\Facades\Log::shouldReceive('info')->zeroOrMoreTimes();
        \Illuminate\Support\Facades\Log::shouldReceive('debug')->zeroOrMoreTimes();
        \Illuminate\Support\Facades\Log::shouldReceive('error')->zeroOrMoreTimes();
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->zeroOrMoreTimes();

        $this->borrowingRepository = \Mockery::mock(BorrowingRepositoryInterface::class);
        $this->eventRepository = \Mockery::mock(EventRepositoryInterface::class);
        $this->activityRepository = \Mockery::mock(ActivityRepositoryInterface::class);
        $this->userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $this->borrowingDocumentRepository = \Mockery::mock(BorrowingDocumentRepositoryInterface::class);

        $this->service = new BorrowingManagementService(
            $this->borrowingRepository,
            $this->eventRepository,
            $this->activityRepository,
            $this->userRepository,
            $this->borrowingDocumentRepository
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(BorrowingManagementService::class, $this->service);
    }

    /** @test */
    public function it_creates_a_new_borrowing_for_an_activity()
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = new \App\Models\User(['id' => 1, 'name' => 'Test User', 'phone' => '1234567890']);
        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($user);

        $activity = new \App\Models\Activity(['id' => 1, 'name' => 'Test Activity', 'location' => 'Test Location']);
        $this->activityRepository->shouldReceive('create')->andReturn($activity);

        $this->borrowingRepository->shouldReceive('create')->andReturn(new \App\Models\Borrowing());

        $data = [
            'borrowable_type' => 'activity',
            'activity_name' => 'Test Activity',
            'activity_location' => 'Test Location',
        ];

        $result = $this->service->createNewBorrowing($data);

        $this->assertInstanceOf(\App\Models\Borrowing::class, $result);
    }

    /** @test */
    public function it_creates_a_new_borrowing_for_an_event()
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = new \App\Models\User(['id' => 1, 'name' => 'Test User', 'phone' => '1234567890']);
        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($user);

        $event = new \App\Models\Event(['id' => 1]);
        $this->eventRepository->shouldReceive('findById')->with(1)->andReturn($event);

        $this->borrowingRepository->shouldReceive('create')->andReturn(new \App\Models\Borrowing());

        $data = [
            'borrowable_type' => 'event',
            'borrowable_id' => 1,
        ];

        $result = $this->service->createNewBorrowing($data);

        $this->assertInstanceOf(\App\Models\Borrowing::class, $result);
    }

    /** @test */
    public function it_throws_an_exception_for_an_invalid_borrowable_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $data = [
            'borrowable_type' => 'invalid_type',
        ];

        $this->service->createNewBorrowing($data);
    }
}
