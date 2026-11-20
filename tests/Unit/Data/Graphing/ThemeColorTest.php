<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\ThemeColor;
use LibreNMS\Tests\TestCase;

class ThemeColorTest extends TestCase
{
    public function test_it_uses_the_variation_of_the_style(): void
    {
        $color = new ThemeColor('111111', '222222');

        $this->assertSame('111111', $color->for(false));
        $this->assertSame('222222', $color->for(true));
    }
}
