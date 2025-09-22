<?php

namespace Tests\Unit\Enums;

use App\Enums\DocumentType;
use PHPUnit\Framework\TestCase;

class DocumentTypeTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Peminjaman', DocumentType::BORROWING->label());
        $this->assertEquals('Perizinan', DocumentType::LICENSING->label());
        $this->assertEquals('Undangan', DocumentType::INVITATION->label());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'peminjaman',
            'perizinan',
            'undangan',
        ], DocumentType::values());
    }
}
