<?php

namespace Tests\Unit\Enums;

use App\Enums\EventRecurrenceType;
use PHPUnit\Framework\TestCase;

class EventRecurrenceTypeTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Daily', EventRecurrenceType::DAILY->label());
        $this->assertEquals('Weekly', EventRecurrenceType::WEEKLY->label());
        $this->assertEquals('Biweekly', EventRecurrenceType::BIWEEKLY->label());
        $this->assertEquals('Monthly', EventRecurrenceType::MONTHLY->label());
        $this->assertEquals('Custom', EventRecurrenceType::CUSTOM->label());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'daily',
            'weekly',
            'biweekly',
            'monthly',
            'custom',
        ], EventRecurrenceType::values());
    }
}
