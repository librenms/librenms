<?php

/**
 * UserFuncHelperTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\UserFuncHelper;
use PHPUnit\Framework\Attributes\DataProvider;

final class UserFuncHelperTest extends TestCase
{
    /**
     * V-Solution / V1600D OLTs report optical power as "x mW (y dBm)".
     * vsolDbm() must return the dBm value from inside the parentheses,
     * falling back to the already-extracted numeric value when absent.
     */
    #[DataProvider('vsolDbmProvider')]
    public function testVsolDbm(float $extracted, ?string $raw, float $expected): void
    {
        $helper = new UserFuncHelper($extracted, $raw);
        $this->assertEqualsWithDelta($expected, $helper->vsolDbm(), 0.001);
    }

    /**
     * @return array<string, array{float, string|null, float}>
     */
    public static function vsolDbmProvider(): array
    {
        return [
            'negative dBm' => [0.0, '0.00 mW (-23.19 dBm)', -23.19],
            'low power' => [0.03, '0.03 mW (-14.61 dBm)', -14.61],
            'no-signal sentinel' => [0.0, '0.00 mW (-30.00 dBm)', -30.0],
            'positive dBm' => [6.55, '6.55 mW (8.16 dBm)', 8.16],
            'empty string' => [0.0, '', 0.0],
            'plain number raw' => [-12.5, '-12.5', -12.5],
        ];
    }
}
