<?php

namespace App\Services;

function toastr() {
    return new class {
        public function success($message) {}
    };
}

namespace Tests\Unit\Services;

use App\Models\Asset;
use App\Models\Location;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Services\DocumentDataCorrectionService;
use PHPUnit\Framework\TestCase;
use Mockery\MockInterface;

class DocumentDataCorrectionServiceTest extends TestCase
{
    protected DocumentDataCorrectionService $service;
    protected MockInterface $assetRepository;
    protected MockInterface $locationRepository;
    protected MockInterface $organizationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetRepository = \Mockery::mock(AssetRepositoryInterface::class);
        $this->locationRepository = \Mockery::mock(LocationRepositoryInterface::class);
        $this->organizationRepository = \Mockery::mock(OrganizationRepositoryInterface::class);

        $this->service = new DocumentDataCorrectionService(
            $this->assetRepository,
            $this->locationRepository,
            $this->organizationRepository
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
        $this->assertInstanceOf(DocumentDataCorrectionService::class, $this->service);
    }

    /** @test */
    public function it_adds_a_new_location()
    {
        $data = ['events' => [[]]];
        $result = $this->service->addLocation($data, 0);
        $this->assertCount(1, $result['events'][0]['location_data']);
    }

    /** @test */
    public function it_removes_a_location()
    {
        $data = ['events' => [['location_data' => [['name' => 'test']]]]];
        $result = $this->service->removeLocation($data, 0, 0);
        $this->assertCount(0, $result['events'][0]['location_data']);
    }

    /** @test */
    public function it_updates_a_location()
    {
        $location = \Mockery::mock(Location::class);
        $location->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $location->shouldReceive('getAttribute')->with('name')->andReturn('New Location');

        $this->locationRepository->shouldReceive('findById')->with(1)->andReturn($location);

        $data = ['events' => [['location_data' => [['name' => 'Old Location']]]]];
        $result = $this->service->updateLocation($data, 0, 0, 1);

        $this->assertEquals('New Location', $result['events'][0]['location_data'][0]['name']);
        $this->assertEquals(1, $result['events'][0]['location_data'][0]['location_id']);
        $this->assertEquals('matched', $result['events'][0]['location_data'][0]['match_status']);
    }

    /** @test */
    public function it_adds_a_date_range()
    {
        $data = ['events' => [[]]];
        $result = $this->service->addDateRange($data, 0);
        $this->assertCount(1, $result['events'][0]['dates']);
    }

    /** @test */
    public function it_removes_a_date_range()
    {
        $data = ['events' => [['dates' => [['start' => '2025-01-01'], ['start' => '2025-01-02']]]]];
        $result = $this->service->removeDateRange($data, 0, 0);
        $this->assertCount(1, $result['events'][0]['dates']);
    }

    /** @test */
    public function it_removes_an_item()
    {
        $data = ['events' => [['equipment' => [['name' => 'test']]]]];
        $result = $this->service->removeItem($data, 'equipment', 0, 0);
        $this->assertCount(0, $result['events'][0]['equipment']);
    }

    /** @test */
    public function it_links_an_item()
    {
        $asset = \Mockery::mock(Asset::class);
        $asset->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $asset->shouldReceive('getAttribute')->with('name')->andReturn('New Asset');

        $this->assetRepository->shouldReceive('findById')->with(1)->andReturn($asset);

        $data = ['events' => [['equipment' => [['name' => 'Old Asset']]]]];
        $result = $this->service->linkItem($data, 'equipment', 1, 0, 0);

        $this->assertEquals('New Asset', $result['events'][0]['equipment'][0]['name']);
        $this->assertEquals(1, $result['events'][0]['equipment'][0]['item_id']);
        $this->assertEquals('matched', $result['events'][0]['equipment'][0]['match_status']);
    }
}
