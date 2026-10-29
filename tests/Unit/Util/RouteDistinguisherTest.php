<?php

/**
 * RouteDistinguisherTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\RouteDistinguisher;
use PHPUnit\Framework\Attributes\DataProvider;

final class RouteDistinguisherTest extends TestCase
{
    /**
     * @return array<string, array{0: string|null, 1: string|null}>
     */
    public static function routeDistinguisherProvider(): array
    {
        return [
            'text' => ['65000:100', '65000:100'],
            'quoted text' => ['"10.246.0.3:10001"', '10.246.0.3:10001'],
            'text with trailing null (VRP)' => ['36 35 33 30 31 3A 31 00', '65301:1'],
            'type 0 (TiMOS, Ironware)' => ['00 00 30 39 00 00 00 6F', '12345:111'],
            'type 0 ending in null' => ['00 00 30 39 00 00 01 00', '12345:256'],
            'type 1' => ['00 01 C0 00 02 01 00 64', '192.0.2.1:100'],
            'type 2' => ['00 02 FA 56 EA 00 00 0A', '4200000000:10'],
            'not set' => ['00 00 00 00 00 00 00 00', null],
            'invalid (Arista)' => ['INVALID', null],
            'empty' => ['', null],
            'null' => [null, null],
            'unknown type' => ['00 09 00 00 00 00 00 01', null],
        ];
    }

    #[DataProvider('routeDistinguisherProvider')]
    public function testParse(?string $value, ?string $expected): void
    {
        $this->assertSame($expected, RouteDistinguisher::parse($value));
    }
}
