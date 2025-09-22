<?php

namespace Tests\Unit\Enums;

use App\Enums\ArticleStatus;
use PHPUnit\Framework\TestCase;

class ArticleStatusTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_label()
    {
        $this->assertEquals('Published', ArticleStatus::PUBLISHED->label());
        $this->assertEquals('Draft', ArticleStatus::DRAFT->label());
        $this->assertEquals('Archived', ArticleStatus::ARCHIVED->label());
    }

    /** @test */
    public function it_returns_the_correct_color()
    {
        $this->assertEquals('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', ArticleStatus::PUBLISHED->color());
        $this->assertEquals('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', ArticleStatus::DRAFT->color());
        $this->assertEquals('bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', ArticleStatus::ARCHIVED->color());
    }

    /** @test */
    public function it_returns_all_values()
    {
        $this->assertEquals([
            'published',
            'draft',
            'archived',
        ], ArticleStatus::values());
    }
}
