<?php
/**
 * InfluxStoreTest.php *
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use InfluxDB\Point;
use LibreNMS\Config;
use LibreNMS\Data\Store\InfluxDB;
use LibreNMS\Tests\TestCase;

/**
 * @group datastores
 */
class InfluxDBStoreTest extends TestCase
{
    public function testBadSettings()
    {
        Config::set('influxdb.host', '');
        Config::set('influxdb.port', 'abc');
        $influx = new InfluxDB(InfluxDB::createFromConfig());

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->once()->with('InfluxDB exception: Unable to parse URI: http://:0'); // the important one
        $influx->put(['hostname' => 'test'], 'fake', [], ['one' => 1]);
    }

    public function testSimpleWrite()
    {
        // Create a mock of the Random Interface
        $mock = \Mockery::mock(\InfluxDB\Database::class);

        $mock->shouldReceive('exists')->once()->andReturn(true);
        $influx = new InfluxDB($mock);

        $device = ['hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234.0, 'ifOut' => 53453.0];

        $expected = [new Point($measurement, null, ['hostname' => $device['hostname']] + $tags, $fields)];

        $mock->shouldReceive('writePoints')->withArgs([$expected])->once();
        $influx->put($device, $measurement, $tags, $fields);
    }
}
