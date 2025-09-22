<?php

namespace Tests\Unit\Services;

use App\DataTransferObjects\LetterData;
use App\DataTransferObjects\PreparationResultData;
use App\Services\DocumentDataPreparationService;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class DocumentDataPreparationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }

    /** @test */
    public function it_prepares_and_normalizes_data_through_pipeline()
    {
        Log::shouldReceive('info');

        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);

        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send')->once()->andReturnSelf();
        $pipelineMock->shouldReceive('through')->once()->andReturnSelf();
        $pipelineMock->shouldReceive('thenReturn')->once()->andReturn(['events' => [], 'processing_errors' => []]);

        app()->instance(Pipeline::class, $pipelineMock);

        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);

        $this->assertInstanceOf(PreparationResultData::class, $result);
        $this->assertFalse($result->hasErrors);
    }

    /** @test */
    public function it_detects_document_date_error()
    {
        Log::shouldReceive('info');
        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);
        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send->through->thenReturn')->andReturn([
            'document_information' => ['document_date' => ['status' => 'error']],
            'events' => [],
            'processing_errors' => []
        ]);
        app()->instance(Pipeline::class, $pipelineMock);
        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);
        $this->assertTrue($result->hasErrors);
    }

    /** @test */
    public function it_detects_event_date_error()
    {
        Log::shouldReceive('info');
        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);
        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send->through->thenReturn')->andReturn([
            'events' => [['parsed_dates' => ['status' => 'error']]],
            'processing_errors' => []
        ]);
        app()->instance(Pipeline::class, $pipelineMock);
        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);
        $this->assertTrue($result->hasErrors);
    }

    /** @test */
    public function it_detects_unmatched_location()
    {
        Log::shouldReceive('info');
        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);
        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send->through->thenReturn')->andReturn([
            'events' => [['location_data' => [['match_status' => 'unmatched']]]],
            'processing_errors' => []
        ]);
        app()->instance(Pipeline::class, $pipelineMock);
        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);
        $this->assertTrue($result->hasErrors);
    }

    /** @test */
    public function it_detects_unmatched_equipment()
    {
        Log::shouldReceive('info');
        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);
        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send->through->thenReturn')->andReturn([
            'events' => [['equipment' => [['match_status' => 'unmatched']]]],
            'processing_errors' => []
        ]);
        app()->instance(Pipeline::class, $pipelineMock);
        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);
        $this->assertTrue($result->hasErrors);
    }

    /** @test */
    public function it_detects_unmatched_organization()
    {
        Log::shouldReceive('info');
        $letterData = \Mockery::mock(LetterData::class);
        $letterData->shouldReceive('toArray')->andReturn([]);
        $pipelineMock = \Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send->through->thenReturn')->andReturn([
            'document_information' => ['emitter_organizations' => [['match_status' => 'unmatched']]],
            'events' => [],
            'processing_errors' => []
        ]);
        app()->instance(Pipeline::class, $pipelineMock);
        $service = new DocumentDataPreparationService();
        $result = $service->prepareAndNormalize($letterData);
        $this->assertTrue($result->hasErrors);
    }
}
