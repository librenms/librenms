<?php

/**
 * RedfishTest.php
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
 * @copyright  2026 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Sensor;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Modules\Sensors;
use LibreNMS\OS;
use LibreNMS\Polling\Method\Methods\RedfishPollingMethod;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use Mockery;

final class RedfishTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    /**
     * The sensor readings the check crawled, in the layout of the Redfish data source.
     *
     * @var list<array{name: string, class: string, value: float|null, warn_low: float|null, warn_high: float|null, crit_low: float|null, crit_high: float|null, chassis: string, source: string}>
     */
    private const array READINGS = [
        [
            'name' => 'CPU Temp',
            'class' => 'temperature',
            'value' => 47.0,
            'warn_low' => null,
            'warn_high' => 80.0,
            'crit_low' => null,
            'crit_high' => 95.0,
            'chassis' => '1',
            'source' => '/Chassis/1/Thermal/CPU Temp',
        ],
        [
            'name' => 'Inlet Temp',
            'class' => 'temperature',
            'value' => 25.5,
            'warn_low' => null,
            'warn_high' => null,
            'crit_low' => null,
            'crit_high' => null,
            'chassis' => '1',
            'source' => '/Chassis/1/Thermal/Inlet Temp',
        ],
        [
            'name' => 'Fan 1',
            'class' => 'fanspeed',
            'value' => 3600.0,
            'warn_low' => 960.0,
            'warn_high' => null,
            'crit_low' => 600.0,
            'crit_high' => null,
            'chassis' => '1',
            'source' => '/Chassis/1/Thermal/Fan 1',
        ],
        [
            'name' => 'Power Supply 1',
            'class' => 'power',
            'value' => 120.5,
            'warn_low' => null,
            'warn_high' => null,
            'crit_low' => null,
            'crit_high' => null,
            'chassis' => '1',
            'source' => '/Chassis/1/Power/Power Supply 1',
        ],
    ];

    public function testRedfishDiscoverAndPoll(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => self::READINGS]);

        $module = new Sensors;
        $module->discover($os);

        $sensors = $os->getDevice()->sensors()->orderBy('sensor_index')->get();
        $this->assertSame(
            ['CPU Temp', 'Inlet Temp', 'Fan 1', 'Power Supply 1'],
            $sensors->pluck('sensor_descr')->all()
        );
        $this->assertSame(['redfish', 'redfish', 'redfish', 'redfish'], $sensors->pluck('poller_type')->all());
        $this->assertSame(
            ['temperature', 'temperature', 'fanspeed', 'power'],
            $sensors->pluck('sensor_class')->all()
        );
        $this->assertEquals([0, 1, 2, 3], $sensors->pluck('sensor_index')->all());
        $this->assertEquals(47, $sensors[0]->sensor_current);
        $this->assertEquals(25.5, $sensors[1]->sensor_current);
        $this->assertEquals(3600, $sensors[2]->sensor_current);
        $this->assertEquals(95, $sensors[0]->sensor_limit);
        $this->assertEquals(80, $sensors[0]->sensor_limit_warn);
        $this->assertEquals(960, $sensors[2]->sensor_limit_low_warn);
        $this->assertEquals(600, $sensors[2]->sensor_limit_low);
        $this->assertTrue($module->dataExists($os->getDevice()));

        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $os->getDevice()->hostname, 'class' => 'temperature', 'type' => 'redfish', 'descr' => 'CPU Temp'] && $fields == ['sensor' => 47]);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $os->getDevice()->hostname, 'class' => 'temperature', 'type' => 'redfish', 'descr' => 'Inlet Temp'] && $fields == ['sensor' => 25.5]);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $os->getDevice()->hostname, 'class' => 'fanspeed', 'type' => 'redfish', 'descr' => 'Fan 1'] && $fields == ['sensor' => 3600]);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $os->getDevice()->hostname, 'class' => 'power', 'type' => 'redfish', 'descr' => 'Power Supply 1'] && $fields == ['sensor' => 120.5]);

        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $datastore);

        $this->assertEquals(
            [47, 25.5, 3600, 120.5],
            $os->getDevice()->sensors()->orderBy('sensor_index')->pluck('sensor_current')->all()
        );
    }

    public function testRediscoveryDoesNotUpdateUnchangedSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => self::READINGS]);

        $module = new Sensors;
        $module->discover($os);
        $sensor_ids = $os->getDevice()->sensors()->orderBy('sensor_index')->pluck('sensor_id')->all();

        $updated = [];
        Event::listen('eloquent.updated: ' . Sensor::class, function (Sensor $sensor) use (&$updated): void {
            $updated[] = $sensor->sensor_descr;
        });

        // a fresh run with the same readings
        $os = $this->freshOs($os);
        $module->discover($os);

        $this->assertSame([], $updated);
        $this->assertSame($sensor_ids, $os->getDevice()->sensors()->orderBy('sensor_index')->pluck('sensor_id')->all());
    }

    public function testRedfishSensorsNeedTheRedfishMethod(): void
    {
        $module = new Sensors;
        $os = $this->makeOs([PollingMethodType::Redfish]);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => self::READINGS]);

        $this->assertTrue($module->shouldDiscover($os));
        $this->assertFalse($module->shouldPoll($os)); // no sensors discovered

        $module->discover($os);
        $this->assertTrue($module->shouldPoll($os));

        // a failure in an earlier poll is checked again
        $os->getDevice()->pollingMethod(PollingMethodType::Redfish)?->update(['last_check_successful' => false]);
        $this->assertTrue($module->shouldPoll($this->freshOs($os)));

        // the Redfish check fails
        $this->fakeMethod(RedfishPollingMethod::class, false);
        $this->assertFalse($module->shouldPoll($this->freshOs($os)));

        // no Redfish polling method
        $os = $this->makeOs([]);
        $this->assertFalse($module->shouldDiscover($os));
        $this->assertFalse($module->shouldPoll($os));
    }

    public function testUnavailableMethodKeepsItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'redfish', 'sensor_class' => 'temperature', 'sensor_index' => '0', 'sensor_type' => 'redfish']);
        $this->fakeMethod(RedfishPollingMethod::class, false);

        $module = new Sensors;
        $this->assertFalse($module->shouldDiscover($os));
        $module->discover($os);

        $this->assertSame(1, Sensor::where('device_id', $os->getDeviceId())->count());
    }

    public function testSensorsWithoutAReadingAreNotRecorded(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => self::READINGS]);

        $module = new Sensors;
        $module->discover($os);

        // the crawl no longer reaches the fan
        $readings = array_map(fn (array $reading) => $reading['name'] === 'Fan 1' ? [...$reading, 'value' => null] : $reading, self::READINGS);
        $os = $this->freshOs($os);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => $readings]);

        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->times(3)->withArgs(fn ($measurement, $fields, $tags) => $tags['type'] == 'redfish' && $tags['descr'] != 'Fan 1');

        $module->poll($os, $datastore);

        $fan = Sensor::where('device_id', $os->getDeviceId())->where('sensor_descr', 'Fan 1')->first();
        $this->assertEquals(3600, $fan->sensor_current); // the last polled value is kept
    }

    public function testEachMethodSyncsOnlyItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'snmp', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'linux']);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'ipmi', 'sensor_class' => 'temperature', 'sensor_index' => '0', 'sensor_type' => 'ipmi']);
        $this->fakeMethod(RedfishPollingMethod::class, true, ['readings' => self::READINGS]);

        (new Sensors)->discover($os);

        // the device has no SNMP or IPMI method, their sensors are kept
        $this->assertSame(['ipmi', 'redfish', 'snmp'], Sensor::where('device_id', $os->getDeviceId())->orderBy('poller_type')->pluck('poller_type')->unique()->values()->all());
    }

    public function testRemovingAMethodDeletesOnlyItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::Redfish]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'snmp', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'linux']);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'redfish', 'sensor_class' => 'temperature', 'sensor_index' => '0', 'sensor_type' => 'redfish']);

        (new Sensors)->removeMethodData($os->getDevice(), PollingMethodType::Redfish);

        $this->assertSame(['snmp'], $os->getDevice()->sensors()->pluck('poller_type')->all());
    }

    /**
     * A fresh run for the same device, like a new discovery/poller invocation
     */
    private function freshOs(OS $os): OS
    {
        $device = DeviceCache::refresh($os->getDeviceId())->toArray();

        return OS::make($device);
    }
}
