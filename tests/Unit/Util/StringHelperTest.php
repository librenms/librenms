<?php

/*
 * StringHelperTest.php
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\StringHelpers;

final class StringHelperTest extends TestCase
{
    public function testValidUtf8(): void
    {
        $this->assertTrue(StringHelpers::isValidUtf8('Øverbyvegen'));
        $this->assertFalse(StringHelpers::isValidUtf8("\xD8verbyvegen"));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testInferEncoding(): void
    {
        $this->assertEquals(null, StringHelpers::inferEncoding(null));
        $this->assertEquals('', StringHelpers::inferEncoding(''));
        $this->assertEquals('~null', StringHelpers::inferEncoding('~null'));
        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding('Øverbyvegen'));
        $this->assertEquals("first\nsecond", StringHelpers::inferEncoding("first\xDAsecond"));

        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding(base64_decode('w5h2ZXJieXZlZ2Vu')));
        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding(base64_decode('2HZlcmJ5dmVnZW4=')));
        $this->assertEquals('教科网IPv4', StringHelpers::inferEncoding(base64_decode('vcy/xs34SVB2NA==')));

        // Huawei SmartAX MA5608T returns GBK-encoded "风机盒" (fan box) in
        // ENTITY-MIB::entPhysicalDescr, which crashed discovery. See #20361
        $this->assertEquals('MA5610&MA5616风机盒', StringHelpers::inferEncoding("MA5610&MA5616\xB7\xE7\xBB\xFA\xBA\xD0"));

        config(['app.charset' => 'Shift_JIS']);
        $this->assertEquals('コンサート', StringHelpers::inferEncoding(base64_decode('g1KDk4NUgVuDZw==')));
    }

    public function testIsStringable(): void
    {
        $this->assertTrue(StringHelpers::isStringable(null));
        $this->assertTrue(StringHelpers::isStringable(''));
        $this->assertTrue(StringHelpers::isStringable('string'));
        $this->assertTrue(StringHelpers::isStringable(-1));
        $this->assertTrue(StringHelpers::isStringable(1.0));
        $this->assertTrue(StringHelpers::isStringable(false));

        $this->assertFalse(StringHelpers::isStringable([]));
        $this->assertFalse(StringHelpers::isStringable((object) []));

        $stringable = new class
        {
            public function __toString()
            {
                return '';
            }
        };
        $this->assertTrue(StringHelpers::isStringable($stringable));

        $nonstringable = new class {
        };
        $this->assertFalse(StringHelpers::isStringable($nonstringable));
    }

    public function testIsHexString(): void
    {
        $this->assertTrue(StringHelpers::isHex('af'));
        $this->assertTrue(StringHelpers::isHex('28'));
        $this->assertTrue(StringHelpers::isHex('aF28'));
        $this->assertFalse(StringHelpers::isHex('a'));
        $this->assertFalse(StringHelpers::isHex('aF 28'));
        $this->assertFalse(StringHelpers::isHex('aF 2'));
        $this->assertFalse(StringHelpers::isHex('aG'));
    }

    public function testIsHexWithDelimiters(): void
    {
        $this->assertTrue(StringHelpers::isHex('af 28 02', ' '));
        $this->assertTrue(StringHelpers::isHex('aF 28 02 CE', ' '));
        $this->assertFalse(StringHelpers::isHex('a5 fj 53', ' '));
        $this->assertFalse(StringHelpers::isHex('a5fe53', ' '));

        $this->assertFalse(StringHelpers::isHex('af 28 02', ':'));
        $this->assertTrue(StringHelpers::isHex('af:28:02', ':'));
        $this->assertTrue(StringHelpers::isHex('aF:28:02:CE', ':'));
        $this->assertFalse(StringHelpers::isHex('a5:fj:53', ':'));
        $this->assertFalse(StringHelpers::isHex('a5fe53', ':'));
    }

    public function testDecodeSnmpHexText(): void
    {
        // off by one string length (trailing null)
        $this->assertSame('24500793', StringHelpers::decodeSnmpHexText('32 34 35 30 30 37 39 33 00'));
        // net-snmp wraps long hex output
        $this->assertSame('DINION IP starlight 7000 HD', StringHelpers::decodeSnmpHexText("44 49 4E 49 4F 4E 20 49 50 20 73 74 61 72 6C 69\n67 68 74 20 37 30 30 30 20 48 44 00"));
        // non-ASCII text
        $this->assertSame('黑色碳粉', StringHelpers::decodeSnmpHexText('E9 BB 91 E8 89 B2 E7 A2 B3 E7 B2 89'));
        $this->assertSame('黑色', StringHelpers::decodeSnmpHexText('E9 BB 91 E8 89 B2 00'));

        // unchanged: not hex, or printable ASCII that net-snmp would not have printed as hex
        $this->assertSame('plain text', StringHelpers::decodeSnmpHexText('plain text'));
        $this->assertSame('41 42', StringHelpers::decodeSnmpHexText('41 42'));
        $this->assertSame('', StringHelpers::decodeSnmpHexText(''));
    }
}
