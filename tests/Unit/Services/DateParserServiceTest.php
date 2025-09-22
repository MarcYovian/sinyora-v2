<?php

namespace Tests\Unit\Services;

use App\Exceptions\ParsingFailedException;
use App\Services\DateParserService;
use PHPUnit\Framework\TestCase;

class DateParserServiceTest extends TestCase
{
    /** @test */
    public function it_parses_a_single_date_correctly()
    {
        $result = DateParserService::parse('7 juli 2025', 'date');

        $this->assertEquals([
            'type' => 'single',
            'date' => '2025-07-07',
        ], $result);
    }

    /** @test */
    public function it_parses_a_date_range_correctly()
    {
        $result = DateParserService::parse('5 - 6 juli 2025', 'date');

        $this->assertEquals([
            'type' => 'range',
            'start_date' => '2025-07-05',
            'end_date' => '2025-07-06',
        ], $result);
    }

    /** @test */
    public function it_parses_a_date_list_correctly()
    {
        $result = DateParserService::parse('02, 09, 16 juni 2024', 'date');

        $this->assertEquals([
            'type' => 'list',
            'dates' => [
                '2024-06-02',
                '2024-06-09',
                '2024-06-16',
            ],
        ], $result);
    }

    /** @test */
    public function it_throws_an_exception_for_invalid_date_format()
    {
        $this->expectException(ParsingFailedException::class);
        DateParserService::parse('invalid date', 'date');
    }

    /** @test */
    public function it_parses_a_single_time_correctly()
    {
        $result = DateParserService::parse('10:00', 'time');

        $this->assertEquals([
            'type' => 'single',
            'time' => '10:00',
        ], $result);
    }

    /** @test */
    public function it_parses_a_time_range_correctly()
    {
        $result = DateParserService::parse('10:00 - 11:00', 'time');

        $this->assertEquals([
            'type' => 'range',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ], $result);
    }

    /** @test */
    public function it_parses_a_time_range_with_selesai_correctly()
    {
        $result = DateParserService::parse('10:00 - selesai', 'time');

        $this->assertEquals([
            'type' => 'range_open_end',
            'start_time' => '10:00',
            'end_time' => 'selesai',
        ], $result);
    }

    /** @test */
    public function it_throws_an_exception_for_invalid_time_format()
    {
        $this->expectException(ParsingFailedException::class);
        DateParserService::parse('invalid time', 'time');
    }

    /** @test */
    public function it_throws_an_exception_for_invalid_field_type()
    {
        $this->expectException(ParsingFailedException::class);
        DateParserService::parse('any string', 'invalid_type');
    }
}
