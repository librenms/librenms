<?php

/*
 * JetdirectMioInfoTest.php
 *
 * Tests parsing of HP-LASERJET-COMMON-MIB::mio*-manufacturing-info values.
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
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\OS\Jetdirect;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class JetdirectMioInfoTest extends TestCase
{
    #[DataProvider('mioValues')]
    public function testParseMioInfo(string $value, ?string $expected): void
    {
        $this->assertSame($expected, Jetdirect::parseMioInfo($value));
    }

    public static function mioValues(): array
    {
        return [
            // M480 HP-LASERJET-COMMON-MIB strings: symbol set 0xFDE8, printed raw or as hex wrapped every 16 bytes
            'symbol set, raw' => ["\xFD\xE8HP Color LaserJet MFP M480", 'HP Color LaserJet MFP M480'],
            'symbol set, wrapped hex' => [
                "FD E8 48 50 20 43 6F 6C 6F 72 20 4C 61 73 65 72 \n4A 65 74 20 4D 46 50 20 4D 34 38 30",
                'HP Color LaserJet MFP M480',
            ],
            // Roman-8 symbol set from the MIB header
            'Roman-8, raw' => ["\x01\x15JD149 FW 2509", 'JD149 FW 2509'],
            'Roman-8, hex with trailing null' => ['01 15 4A 44 31 34 39 00', 'JD149'],
            'no symbol set' => ['JD149 rev 1.0', 'JD149 rev 1.0'],
            // M480 mio4 on successive discoveries: '?' placeholders and a few bytes that change on every query
            'placeholders' => ['FD E8 3F 3F 3F 3F 3F 3F 3F 3F 3F 3F 3F', null],
            'placeholders, short' => ['FD E8 3F 3F 3F', null],
            'placeholders with stray text' => ['FD E8 3F 3F 6F 3F 3F 3F 3F 3F 6F 3F', null],
            'placeholders with stray space' => ['FD E8 3F 3F 6F 3F 20 3F 3F 3F 3F 6F 3F', null],
            'placeholders with control byte' => ['FD E8 3F 3F 3F 3F 70 3F 3F 01 3F 3F 3F 3F', null],
            'placeholders with DEL byte' => ['FD E8 3F 3F 1F 3F 7F 76 01 3F 3F 1F 3F', null],
            'placeholders, wrapped hex' => ["FD E8 3F 3F 6F 3F 3F 3F 3F 3F 6F 3F 3F 01 3F 3F \n3F 70 3F 3F", null],
            'empty' => ['', null],
        ];
    }
}
