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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Util\Rewrite;

class FunctionsTest extends TestCase
{
    public function testMacCleanToReadable()
    {
        $this->assertEquals('de:ad:be:ef:a0:c3', Rewrite::readableMac('deadbeefa0c3'));
    }

    public function testHex2Str()
    {
        $this->assertEquals('Big 10 UP', hex2str('426967203130205550'));
    }

    public function testSnmpHexstring()
    {
        $input = '4c 61 72 70 69 6e 67 20 34 20 55 00 0a';
        $this->assertEquals("Larping 4 U\n", snmp_hexstring($input));
    }

    public function testIsHexString()
    {
        $this->assertTrue(isHexString('af 28 02'));
        $this->assertTrue(isHexString('aF 28 02 CE'));
        $this->assertFalse(isHexString('a5 fj 53'));
        $this->assertFalse(isHexString('a5fe53'));
    }

    public function testLinkify()
    {
        $input = 'foo@demo.net	bar.ba@test.co.uk
www.demo.com	http://foo.co.uk/
sdfsd ftp://192.168.1.1/help/me/now.php
http://regexr.com/foo.html?q=bar
https://mediatemple.net.';

        $expected = 'foo@demo.net	bar.ba@test.co.uk
www.demo.com	<a href="http://foo.co.uk/">http://foo.co.uk/</a>
sdfsd <a href="ftp://192.168.1.1/help/me/now.php">ftp://192.168.1.1/help/me/now.php</a>
<a href="http://regexr.com/foo.html?q=bar">http://regexr.com/foo.html?q=bar</a>
<a href="https://mediatemple.net">https://mediatemple.net</a>.';

        $this->assertSame($expected, linkify($input));
    }

    public function testDynamicDiscoveryGetValue()
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

    public function testParseAtTime()
    {
        $this->assertEquals(time(), parse_at_time('now'), 'now did not match');
        $this->assertEquals(time() + 180, parse_at_time('+3m'), '+3m did not match');
        $this->assertEquals(time() + 7200, parse_at_time('+2h'), '+2h did not match');
        $this->assertEquals(time() + 172800, parse_at_time('+2d'), '+2d did not match');
        $this->assertEquals(time() + 63115200, parse_at_time('+2y'), '+2y did not match');
        $this->assertEquals(time() - 180, parse_at_time('-3m'), '-3m did not match');
        $this->assertEquals(time() - 7200, parse_at_time('-2h'), '-2h did not match');
        $this->assertEquals(time() - 172800, parse_at_time('-2d'), '-2d did not match');
        $this->assertEquals(time() - 63115200, parse_at_time('-2y'), '-2y did not match');
        $this->assertEquals(429929439, parse_at_time('429929439'));
        $this->assertEquals(212334234, parse_at_time(212334234));
        $this->assertEquals(time() - 43, parse_at_time('-43'), '-43 did not match');
        $this->assertEquals(0, parse_at_time('invalid'));
        $this->assertEquals(606614400, parse_at_time('March 23 1989 UTC'));
        $this->assertEquals(time() + 86400, parse_at_time('+1 day'));
    }
}
