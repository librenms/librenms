<?php

/**
 * ColorTest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Util\Color;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    /**
     * Every percentage must produce a valid #rrggbb color.
     * Utilizations below 25% previously overflowed the red channel negative,
     * producing an invalid string like "#ffffffffffffff838282".
     */
    #[DataProvider('percentProvider')]
    public function testPercentIsAlwaysValidHexColor(int $percent): void
    {
        $color = Color::percent($percent, 100);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $color, "percent=$percent produced an invalid color: $color");
    }

    public static function percentProvider(): array
    {
        $cases = [];
        for ($p = 0; $p <= 100; $p += 5) {
            $cases["percent_$p"] = [$p];
        }

        return $cases;
    }

    /**
     * Spot-check the gradient endpoints and the low-utilization range that was broken.
     */
    public function testPercentGradientValues(): void
    {
        // low utilization: red channel clamped at 0, cool (teal) tones
        $this->assertSame('#008282', Color::percent(0, 100));
        $this->assertSame('#005050', Color::percent(10, 100));
        $this->assertSame('#001e1e', Color::percent(20, 100));
        $this->assertSame('#000505', Color::percent(25, 100));

        // high utilization: unchanged, warms up to full red
        $this->assertSame('#7d0000', Color::percent(50, 100));
        $this->assertSame('#ff0000', Color::percent(100, 100));
    }
}
