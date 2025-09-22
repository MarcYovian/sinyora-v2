<?php

namespace Tests\Unit\Services;

use App\Services\BlendColorService;
use PHPUnit\Framework\TestCase;

class BlendColorServiceTest extends TestCase
{
    /** @test */
    public function it_returns_default_color_when_no_colors_are_provided()
    {
        $this->assertEquals(BlendColorService::DEFAULT_COLOR, BlendColorService::blend([]));
    }

    /** @test */
    public function it_returns_the_same_color_when_only_one_color_is_provided()
    {
        $this->assertEquals('#FF0000', BlendColorService::blend(['#FF0000']));
    }

    /** @test */
    public function it_blends_two_colors_correctly()
    {
        // Merah (#FF0000) dan Biru (#0000FF) harus menghasilkan Ungu (#800080)
        $this->assertEquals('#800080', BlendColorService::blend(['#FF0000', '#0000FF']));
    }

    /** @test */
    public function it_blends_multiple_colors_correctly()
    {
        // Red, Green, Blue
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        // Average should be gray
        $this->assertEquals('#555555', BlendColorService::blend($colors));
    }

    /** @test */
    public function it_uses_custom_default_color()
    {
        $this->assertEquals('#000000', BlendColorService::blend([], '#000000'));
    }

    /** @test */
    public function it_handles_colors_without_hash_symbol()
    {
        $this->assertEquals('#800080', BlendColorService::blend(['FF0000', '0000FF']));
    }
}
