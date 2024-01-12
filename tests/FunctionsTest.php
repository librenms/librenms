<?php
/**
 * FunctionsTest.php
 *
 * tests functions in includes/functions.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Enum\IntegerType;
use LibreNMS\Util\Number;
use LibreNMS\Util\Time;

class FunctionsTest extends TestCase
{
    public function testHex2Str(): void
    {
        $this->assertEquals('Big 10 UP', hex2str('426967203130205550'));
    }

    public function testSnmpHexstring(): void
    {
        $input = '4c 61 72 70 69 6e 67 20 34 20 55 00 0a';
        $this->assertEquals("Larping 4 U\n", snmp_hexstring($input));
    }

    public function testIsHexString(): void
    {
        $this->assertTrue(isHexString('af 28 02'));
        $this->assertTrue(isHexString('aF 28 02 CE'));
        $this->assertFalse(isHexString('a5 fj 53'));
        $this->assertFalse(isHexString('a5fe53'));
    }

    public function testDynamicDiscoveryGetValue(): void
    {
        $pre_cache = [
            'firstdata' => [
                0 => ['temp' => 1],
                1 => ['temp' => 2],
            ],
            'high' => [
                0 => ['high' => 3],
                1 => ['high' => 4],
            ],
            'table' => [
                0 => ['first' => 5, 'second' => 6],
                1 => ['first' => 7, 'second' => 8],
            ],
            'single' => ['something' => 9],
            'oneoff' => 10,
            'singletable' => [
                11 => ['singletable' => 'Pickle'],
            ],
            'doubletable' => [
                12 => ['doubletable' => 'Mustard'],
                13 => ['doubletable' => 'BBQ'],
            ],
        ];

        $data = ['value' => 'temp', 'oid' => 'firstdata'];
        $this->assertNull(YamlDiscovery::getValueFromData('missing', 0, $data, $pre_cache));
        $this->assertSame('yar', YamlDiscovery::getValueFromData('default', 0, $data, $pre_cache, 'yar'));
        $this->assertSame(2, YamlDiscovery::getValueFromData('value', 1, $data, $pre_cache));

        $data = ['oid' => 'high'];
        $this->assertSame(3, YamlDiscovery::getValueFromData('high', 0, $data, $pre_cache));

        $data = ['oid' => 'table'];
        $this->assertSame(5, YamlDiscovery::getValueFromData('first', 0, $data, $pre_cache));
        $this->assertSame(7, YamlDiscovery::getValueFromData('first', 1, $data, $pre_cache));
        $this->assertSame(6, YamlDiscovery::getValueFromData('second', 0, $data, $pre_cache));
        $this->assertSame(8, YamlDiscovery::getValueFromData('second', 1, $data, $pre_cache));

        $this->assertSame(9, YamlDiscovery::getValueFromData('single', 0, $data, $pre_cache));
        $this->assertSame(10, YamlDiscovery::getValueFromData('oneoff', 3, $data, $pre_cache));
        $this->assertSame('Pickle', YamlDiscovery::getValueFromData('singletable', 11, $data, $pre_cache));
        $this->assertSame('BBQ', YamlDiscovery::getValueFromData('doubletable', 13, $data, $pre_cache));
    }

    public function testParseAtTime(): void
    {
        $this->assertEquals(time(), Time::parseAt('now'), 'now did not match');
        $this->assertEquals(time() + 180, Time::parseAt('+3m'), '+3m did not match');
        $this->assertEquals(time() + 7200, Time::parseAt('+2h'), '+2h did not match');
        $this->assertEquals(time() + 172800, Time::parseAt('+2d'), '+2d did not match');
        $this->assertEquals(time() + 63115200, Time::parseAt('+2y'), '+2y did not match');
        $this->assertEquals(time() - 180, Time::parseAt('-3m'), '-3m did not match');
        $this->assertEquals(time() - 7200, Time::parseAt('-2h'), '-2h did not match');
        $this->assertEquals(time() - 172800, Time::parseAt('-2d'), '-2d did not match');
        $this->assertEquals(time() - 63115200, Time::parseAt('-2y'), '-2y did not match');
        $this->assertEquals(429929439, Time::parseAt('429929439'));
        $this->assertEquals(212334234, Time::parseAt(212334234));
        $this->assertEquals(time() - 43, Time::parseAt('-43'), '-43 did not match');
        $this->assertEquals(0, Time::parseAt('invalid'));
        $this->assertEquals(606614400, Time::parseAt('March 23 1989 UTC'));
        $this->assertEquals(time() + 86400, Time::parseAt('+1 day'));
    }

    public function testNumberCast()
    {
        $this->assertSame(-14.3, Number::cast(-14.3));
        $this->assertSame(0, Number::cast('b -35')); // cast must start with the number as old style php cast did
        $this->assertSame(0, Number::cast('0 43 51'));
        $this->assertSame(14.35, Number::cast('14.35 a'));
        $this->assertSame(-43.332, Number::cast('-43.332 a'));
        $this->assertSame(-12325234523.43, Number::cast('-12325234523.43asdf'));
        $this->assertSame(1, Number::cast(1.0));
        $this->assertSame(2, Number::cast('2.000'));
    }

    public function testNumberAsUnsigned()
    {
        $this->assertSame(42, Number::constrainInteger('42', IntegerType::int32));  /** @phpstan-ignore-line */
        $this->assertSame(2147483647, Number::constrainInteger(2147483647, IntegerType::int32));
        $this->assertSame(-2147483648, Number::constrainInteger(2147483648, IntegerType::int32));
        $this->assertSame(-2147483647, Number::constrainInteger(2147483649, IntegerType::int32));
        $this->assertSame(-1, Number::constrainInteger(4294967295, IntegerType::int32));
        $this->assertSame(-3757, Number::constrainInteger(61779, IntegerType::int16));
        $this->assertSame(0, Number::constrainInteger(0, IntegerType::uint32));
        $this->assertSame(42, Number::constrainInteger(42, IntegerType::uint32));
        $this->assertSame(4294967252, Number::constrainInteger(-42, IntegerType::uint32));
        $this->assertSame(2147483648, Number::constrainInteger(-2147483646, IntegerType::uint32));
        $this->assertSame(2147483647, Number::constrainInteger(-2147483647, IntegerType::uint32));
        $this->assertSame(2147483646, Number::constrainInteger(-2147483648, IntegerType::uint32));
        $this->assertSame(2147483645, Number::constrainInteger(-2147483649, IntegerType::uint32));
    }

    public function testNumberAsUnsignedValueExceedsMaxUnsignedValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Exceeds the maximum representable value for a 16-bit unsigned integer
        Number::constrainInteger(4294967296, IntegerType::int16);
    }
}
