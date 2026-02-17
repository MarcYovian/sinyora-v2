<?php

namespace Tests\Unit\Services;

use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\DocumentManagementService;
use PHPUnit\Framework\TestCase;
use Mockery\MockInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use App\Events\DocumentProposalCreated;
use App\Models\GuestSubmitter;
use App\Models\User;

class DocumentManagementServiceTest extends TestCase
{
    protected DocumentManagementService $service;
    protected MockInterface $documentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentRepository = \Mockery::mock(DocumentRepositoryInterface::class);

        $this->service = new DocumentManagementService(
            $this->documentRepository
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
        $this->assertInstanceOf(DocumentManagementService::class, $this->service);
    }

    /** @test */
    public function it_stores_a_new_document_for_guest()
    {
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $storageDiskMock = \Mockery::mock();
        $storageDiskMock->shouldReceive('mimeType')->andReturn('application/pdf');
        Storage::shouldReceive('disk')->with('public')->andReturn($storageDiskMock);

        $this->documentRepository->shouldReceive('create')->once()->andReturn(new \App\Models\Document());

        Event::shouldReceive('dispatch')->once();

        $submitter = new GuestSubmitter();

        $result = $this->service->storeNewDocument($file, $submitter);

        $this->assertInstanceOf(\App\Models\Document::class, $result);
    }

    /** @test */
    public function it_stores_a_new_document_for_user()
    {
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $storageDiskMock = \Mockery::mock();
        $storageDiskMock->shouldReceive('mimeType')->andReturn('application/pdf');
        Storage::shouldReceive('disk')->with('public')->andReturn($storageDiskMock);

        $this->documentRepository->shouldReceive('create')->once()->andReturn(new \App\Models\Document());

        Event::shouldReceive('dispatch')->never();

        $submitter = new User();

        $result = $this->service->storeNewDocument($file, $submitter);

        $this->assertInstanceOf(\App\Models\Document::class, $result);
    }

    /** @test */
    public function it_deletes_a_document()
    {
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $storageDiskMock = \Mockery::mock();
        $storageDiskMock->shouldReceive('delete')->once()->andReturn(true);
        Storage::shouldReceive('disk')->with('public')->andReturn($storageDiskMock);

        $this->documentRepository->shouldReceive('delete')->once()->andReturn(true);

        $document = new \App\Models\Document(['id' => 1, 'document_path' => 'path/to/doc']);

        $result = $this->service->deleteDocument($document);

        $this->assertTrue($result);
    }
}