<?php

namespace Tests\Unit\Services;

use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\BorrowingManagementService;
use App\Services\DocumentFinalizationService;
use App\Services\EventCreationService;
use App\Services\invitationCreationService;
use PHPUnit\Framework\TestCase;
use Mockery\MockInterface;

class DocumentFinalizationServiceTest extends TestCase
{
    protected DocumentFinalizationService $service;
    protected MockInterface $documentRepository;
    protected MockInterface $eventCreationService;
    protected MockInterface $borrowingManagementService;
    protected MockInterface $invitationCreationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentRepository = \Mockery::mock(DocumentRepositoryInterface::class);
        $this->eventCreationService = \Mockery::mock(EventCreationService::class);
        $this->borrowingManagementService = \Mockery::mock(BorrowingManagementService::class);
        $this->invitationCreationService = \Mockery::mock(invitationCreationService::class);

        $this->service = new DocumentFinalizationService(
            $this->documentRepository,
            $this->eventCreationService,
            $this->borrowingManagementService,
            $this->invitationCreationService
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
        $this->assertInstanceOf(DocumentFinalizationService::class, $this->service);
    }

    /** @test */
    public function it_finalizes_a_licensing_document()
    {
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);

        $documentMock = \Mockery::mock(\App\Models\Document::class);
        $documentMock->shouldReceive('update')->once();
        $documentMock->shouldReceive('signatures->delete')->once();
        $documentMock->shouldReceive('signatures->createMany')->once();

        $this->documentRepository->shouldReceive('findOrFail')->andReturn($documentMock);

        $this->eventCreationService->shouldReceive('createEventFromDocument')->once();
        $this->borrowingManagementService->shouldNotReceive('createBorrowingFromDocument');
        $this->invitationCreationService->shouldNotReceive('createInvitationFromDocument');

        $data = [
            'id' => 1,
            'type' => \App\Enums\DocumentType::LICENSING->value,
            'events' => [[]],
            'signature_blocks' => [],
            'document_information' => [],
        ];

        $this->service->finalize($data);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_finalizes_a_borrowing_document()
    {
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);

        $documentMock = \Mockery::mock(\App\Models\Document::class);
        $documentMock->shouldReceive('update')->once();
        $documentMock->shouldReceive('signatures->delete')->once();
        $documentMock->shouldReceive('signatures->createMany')->once();

        $this->documentRepository->shouldReceive('findOrFail')->andReturn($documentMock);

        $this->eventCreationService->shouldNotReceive('createEventFromDocument');
        $this->borrowingManagementService->shouldReceive('createBorrowingFromDocument')->once();
        $this->invitationCreationService->shouldNotReceive('createInvitationFromDocument');

        $data = [
            'id' => 1,
            'type' => \App\Enums\DocumentType::BORROWING->value,
            'events' => [[]],
            'signature_blocks' => [],
        ];

        $this->service->finalize($data);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_finalizes_an_invitation_document()
    {
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);

        $documentMock = \Mockery::mock(\App\Models\Document::class);
        $documentMock->shouldReceive('update')->once();
        $documentMock->shouldReceive('signatures->delete')->once();
        $documentMock->shouldReceive('signatures->createMany')->once();

        $this->documentRepository->shouldReceive('findOrFail')->andReturn($documentMock);

        $this->eventCreationService->shouldNotReceive('createEventFromDocument');
        $this->borrowingManagementService->shouldNotReceive('createBorrowingFromDocument');
        $this->invitationCreationService->shouldReceive('createInvitationFromDocument')->once();

        $data = [
            'id' => 1,
            'type' => \App\Enums\DocumentType::INVITATION->value,
            'events' => [[]],
            'signature_blocks' => [],
            'document_information' => [],
        ];

        $this->service->finalize($data);

        $this->assertTrue(true);
    }
}
