<?php

namespace Tests\Unit\Enums;

use App\Enums\DocumentStatus;
use PHPUnit\Framework\TestCase;

class DocumentStatusTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Pending', DocumentStatus::PENDING->label());
        $this->assertEquals('Processed', DocumentStatus::PROCESSED->label());
        $this->assertEquals('Done', DocumentStatus::DONE->label());
    }

    /** @test */
    public function it_returns_the_correct_color()
    {
        $this->assertEquals('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', DocumentStatus::PENDING->color());
        $this->assertEquals('bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', DocumentStatus::PROCESSED->color());
        $this->assertEquals('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', DocumentStatus::DONE->color());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'pending',
            'processed',
            'done',
        ], DocumentStatus::values());
    }
}
