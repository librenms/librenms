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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use InfluxDB\Point;
use LibreNMS\Data\Store\InfluxDB;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('datastores')]
class InfluxDBStoreTest extends TestCase
{
    public function testBadSettings(): void
    {
        LibrenmsConfig::set('influxdb.host', '');
        LibrenmsConfig::set('influxdb.port', 'abc');
        $influx = new InfluxDB(InfluxDB::createFromConfig());

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->once()->with('InfluxDB batch write failed: Unable to parse URI: http://:0');
        $influx->write('fake', ['one' => 1]);
    }

    public function testSimpleWrite(): void
    {
        // Create a mock of the Random Interface
        $mock = \Mockery::mock(\InfluxDB\Database::class);
        $mock->shouldReceive('exists')->once()->andReturn(true);
        $influx = new InfluxDB($mock);

        $device = new Device(['hostname' => 'testhost']);
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234.0, 'ifOut' => 53453.0];
        $meta = ['device' => $device];

        $mock->shouldReceive('writePoints')
            ->with(\Mockery::on(function ($points) use ($measurement, $tags, $fields, $device) {
                if (! is_array($points) || count($points) !== 1) {
                    return false;
                }
                $point = $points[0];

                return $point instanceof Point
                    && $point->getMeasurement() === $measurement
                    && $point->getTags() == (['hostname' => $device->hostname] + $tags)
                    && $point->getFields() == $fields;
            }), 's') // Expects second precision
            ->once();
        $influx->write($measurement, $fields, $tags, $meta);
    }

    public function testFilteredMeasurementsAllowed(): void
    {
        LibrenmsConfig::set('influxdb.measurements', ['testmeasure', 'anothermeasure']);

        // Create a mock of the Random Interface
        $mock = \Mockery::mock(\InfluxDB\Database::class);
        $mock->shouldReceive('exists')->once()->andReturn(true);
        $influx = new InfluxDB($mock);

        $device = new Device(['hostname' => 'testhost']);
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234.0, 'ifOut' => 53453.0];
        $meta = ['device' => $device];

        $mock->shouldReceive('writePoints')->once();
        $influx->write($measurement, $fields, $tags, $meta);
    }

    public function testFilteredMeasurementsRejected(): void
    {
        LibrenmsConfig::set('influxdb.measurements', ['anothermeasure', 'yetanothermeasure']);

        // Create a mock of the Random Interface
        $mock = \Mockery::mock(\InfluxDB\Database::class);
        $mock->shouldReceive('exists')->once()->andReturn(true);
        $influx = new InfluxDB($mock);

        $device = new Device(['hostname' => 'testhost']);
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234.0, 'ifOut' => 53453.0];
        $meta = ['device' => $device];

        $mock->shouldReceive('writePoints')->never();
        $influx->write($measurement, $fields, $tags, $meta);
    }
}
