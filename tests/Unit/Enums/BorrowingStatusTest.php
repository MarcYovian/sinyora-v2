<?php

namespace Tests\Unit\Enums;

use App\Enums\BorrowingStatus;
use PHPUnit\Framework\TestCase;

class BorrowingStatusTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Pending', BorrowingStatus::PENDING->label());
        $this->assertEquals('Approved', BorrowingStatus::APPROVED->label());
        $this->assertEquals('Rejected', BorrowingStatus::REJECTED->label());
    }

    /** @test */
    public function it_returns_the_correct_color()
    {
        $this->assertEquals('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', BorrowingStatus::PENDING->color());
        $this->assertEquals('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', BorrowingStatus::APPROVED->color());
        $this->assertEquals('bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', BorrowingStatus::REJECTED->color());
    }

    /** @test */
    public function it_returns_the_correct_border_color()
    {
        $this->assertEquals('border-l-yellow-400 dark:border-l-yellow-600', BorrowingStatus::PENDING->borderColor());
        $this->assertEquals('border-l-green-500 dark:border-l-green-600', BorrowingStatus::APPROVED->borderColor());
        $this->assertEquals('border-l-red-500 dark:border-l-red-600', BorrowingStatus::REJECTED->borderColor());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'pending',
            'approved',
            'rejected',
        ], BorrowingStatus::values());
    }
}
