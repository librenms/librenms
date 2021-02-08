<?php
/**
 * RrdDefinitonTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;

class RrdDefinitionTest extends TestCase
{
    public function testEmpty()
    {
        $this->assertEmpty((string) new RrdDefinition());
    }

    public function testWrongType()
    {
        $this->expectException(\LibreNMS\Exceptions\InvalidRrdTypeException::class);
        Config::set('rrd.step', 300);
        Config::set('rrd.heartbeat', 600);
        $def = new RrdDefinition();
        $def->addDataset('badtype', 'Something unexpected');
    }

    public function testNameEscaping()
    {
        Config::set('rrd.step', 300);
        Config::set('rrd.heartbeat', 600);
        $expected = 'DS:bad_name-is_too_lon:GAUGE:600:0:100 ';
        $def = RrdDefinition::make()->addDataset('b a%d$_n:a^me-is_too_lon%g.', 'GAUGE', 0, 100, 600);

        $this->assertEquals($expected, (string) $def);
    }

    public function testCreation()
    {
        Config::set('rrd.step', 300);
        Config::set('rrd.heartbeat', 600);
        $expected = 'DS:pos:COUNTER:600:0:125000000000 ' .
            'DS:unbound:DERIVE:600:U:U ';

        $def = new RrdDefinition();
        $def->addDataset('pos', 'COUNTER', 0, 125000000000);
        $def->addDataset('unbound', 'DERIVE');

        $this->assertEquals($expected, $def);
    }
}
