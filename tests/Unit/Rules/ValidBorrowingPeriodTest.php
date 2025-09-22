<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidBorrowingPeriod;
use PHPUnit\Framework\TestCase;

class ValidBorrowingPeriodTest extends TestCase
{
    /** @test */
    public function it_fails_if_dates_are_not_provided()
    {
        $rule = new ValidBorrowingPeriod(null, null);
        $fail = fn ($message) => $this->assertEquals('Tanggal mulai dan selesai harus diisi.', $message);
        $rule->validate('start_datetime', '2025-01-01', $fail);
    }

    /** @test */
    public function it_fails_if_start_date_is_after_end_date()
    {
        $rule = new ValidBorrowingPeriod('2025-01-02', '2025-01-01');
        $fail = fn ($message) => $this->assertEquals('Tanggal mulai harus sebelum tanggal selesai.', $message);
        $rule->validate('start_datetime', '2025-01-02', $fail);
    }

    /** @test */
    public function it_fails_if_end_date_is_before_start_date()
    {
        $rule = new ValidBorrowingPeriod('2025-01-02', '2025-01-01');
        $fail = fn ($message) => $this->assertEquals('Tanggal selesai harus setelah tanggal mulai.', $message);
        $rule->validate('end_datetime', '2025-01-01', $fail);
    }

    /** @test */
    public function it_fails_if_period_is_more_than_3_months()
    {
        $rule = new ValidBorrowingPeriod('2025-01-01', '2025-04-02');
        $fail = fn ($message) => $this->assertEquals('Maksimal periode peminjaman adalah 3 bulan.', $message);
        $rule->validate('end_datetime', '2025-04-02', $fail);
    }

    /** @test */
    public function it_passes_with_valid_period()
    {
        $rule = new ValidBorrowingPeriod('2025-01-01', '2025-01-02');
        $fail = fn ($message) => $this->fail("Validation failed unexpectedly: $message");
        $rule->validate('start_datetime', '2025-01-01', $fail);
        $rule->validate('end_datetime', '2025-01-02', $fail);
        $this->assertTrue(true);
    }
}
