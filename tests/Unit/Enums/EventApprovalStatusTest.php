<?php

namespace Tests\Unit\Enums;

use App\Enums\EventApprovalStatus;
use PHPUnit\Framework\TestCase;

class EventApprovalStatusTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Pending', EventApprovalStatus::PENDING->label());
        $this->assertEquals('Approved', EventApprovalStatus::APPROVED->label());
        $this->assertEquals('Rejected', EventApprovalStatus::REJECTED->label());
    }

    /** @test */
    public function it_returns_the_correct_color()
    {
        $this->assertEquals('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', EventApprovalStatus::PENDING->color());
        $this->assertEquals('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', EventApprovalStatus::APPROVED->color());
        $this->assertEquals('bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', EventApprovalStatus::REJECTED->color());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'pending',
            'approved',
            'rejected',
        ], EventApprovalStatus::values());
    }
}
