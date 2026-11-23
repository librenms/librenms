<?php

/**
 * SensorsTest.php
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

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Sensor;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Process;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Modules\Sensors;
use LibreNMS\OS;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use Mockery;

final class SensorsTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    private const string SENSOR_OUTPUT = <<<'EOT'
FAN1             | 3600.000   | RPM        | ok    | 300.000   | 500.000   | 700.000   | 25300.000 | 25400.000 | 25500.000
CPU Temp         | 45.000     | degrees C  | ok    | na        | na        | na        | 85.000    | 90.000    | 95.000
Chassis Intru    | 0x0        | discrete   | 0x0080| na        | na        | na        | na        | na        | na
12V              | na         | Volts      | na    | na        | na        | na        | na        | na        | na
VCORE            | 1.310      | Volts      | ok    | na        | 0.680     | 0.700     | 1.450     | 1.520     | na
EOT;

    private const string SDR_OUTPUT = <<<'EOT'
CPU Temp,47,degrees C,ok,
FAN1,E1h,RPM,ok,
PS Status,0x01,discrete,ok,
EOT;

    private Device $device;
    private DevicePollingMethod $ipmiMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'linux']);
        $this->ipmiMethod = DevicePollingMethod::factory()->create([
            'device_id' => $this->device->device_id,
            'method_type' => PollingMethodType::Ipmi,
            'affects_availability' => false,
            'last_check_successful' => true,
            'settings' => ['hostname' => 'bmc.example.com', 'type' => 'lanplus'],
        ]);
        DeviceCache::setPrimary($this->device->device_id);

        Process::fake(fn (PendingProcess $process) => in_array('sdr', (array) $process->command)
            ? self::SDR_OUTPUT
            : self::SENSOR_OUTPUT);
    }

    public function testIpmiDiscoverAndPoll(): void
    {
        $module = new Sensors;
        $module->discover($this->os());

        Process::assertRan(fn (PendingProcess $process) => ($process->environment['LC_ALL'] ?? null) === 'C');

        $sensors = $this->device->sensors()->orderBy('sensor_index')->get();
        $this->assertSame(['CPU Temp', 'FAN1', 'VCORE'], $sensors->pluck('sensor_descr')->all());
        $this->assertSame(['temperature', 'fanspeed', 'voltage'], $sensors->pluck('sensor_class')->all());
        // discrete sensors consume an index so existing sensors keep their index
        $this->assertEquals([0, 2, 3], $sensors->pluck('sensor_index')->all());
        $this->assertEquals(45, $sensors[0]->sensor_current);
        $this->assertEquals(90, $sensors[0]->sensor_limit);
        $this->assertEquals(500, $sensors[1]->sensor_limit_low);
        $this->assertEquals(1.52, $sensors[2]->sensor_limit);
        $this->assertEquals(1.45, $sensors[2]->sensor_limit_warn);
        $this->assertEquals(0.68, $sensors[2]->sensor_limit_low);
        $this->assertEquals(0.7, $sensors[2]->sensor_limit_low_warn);
        $this->assertTrue($module->dataExists($this->device));

        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $this->device->hostname, 'class' => 'temperature', 'type' => 'ipmi', 'descr' => 'CPU Temp'] && $fields == ['sensor' => 47]);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $measurement == 'sensor'
            && $tags == ['hostname' => $this->device->hostname, 'class' => 'fanspeed', 'type' => 'ipmi', 'descr' => 'FAN1'] && $fields == ['sensor' => 225]);

        $module->poll($this->os(), $datastore);

        $this->assertEquals([47, 225, 1.31], $this->device->sensors()->orderBy('sensor_index')->pluck('sensor_current')->all());
    }

    public function testIpmiPortAndTimeoutAreLeftToIpmitoolUnlessSet(): void
    {
        (new Sensors)->discover($this->os());

        Process::assertRan(fn (PendingProcess $process) => ! in_array('-p', (array) $process->command) && ! in_array('-N', (array) $process->command));

        $this->ipmiMethod->update(['settings' => ['hostname' => 'bmc.example.com', 'type' => 'lanplus', 'port' => 6230, 'timeout' => 5]]);
        (new Sensors)->discover($this->os());

        Process::assertRan(function (PendingProcess $process): bool {
            $command = implode(' ', (array) $process->command);

            return str_contains($command, '-p 6230') && str_contains($command, '-N 5');
        });
    }

    public function testRediscoveryDoesNotUpdateUnchangedSensors(): void
    {
        $module = new Sensors;
        $module->discover($this->os());
        $sensor_ids = $this->device->sensors()->orderBy('sensor_index')->pluck('sensor_id')->all();

        $updated = [];
        Event::listen('eloquent.updated: ' . Sensor::class, function (Sensor $sensor) use (&$updated): void {
            $updated[] = $sensor->sensor_descr;
        });

        $module->discover($this->os());

        $this->assertSame([], $updated);
        $this->assertSame($sensor_ids, $this->device->sensors()->orderBy('sensor_index')->pluck('sensor_id')->all());
    }

    public function testIpmiDiscoverHandlesEmptyAndShortOutput(): void
    {
        Process::fake(fn () => "\nCPU Temp | 45.000 | degrees C | ok\n\n");

        (new Sensors)->discover($this->os());

        $sensor = $this->device->sensors()->sole();
        $this->assertSame('CPU Temp', $sensor->sensor_descr);
        $this->assertEquals(45, $sensor->sensor_current);
        $this->assertNull($sensor->sensor_limit_warn);

        // ipmitool reports nothing when the BMC is not responding, the sensors are kept
        Process::fake(fn () => '');
        (new Sensors)->discover($this->os());

        $this->assertSame(1, $this->device->sensors()->count());
    }

    public function testIpmiSensorsNeedTheIpmiMethod(): void
    {
        $module = new Sensors;

        $this->assertTrue($module->shouldDiscover($this->os()));
        $this->assertFalse($module->shouldPoll($this->os())); // no sensors discovered

        $module->discover($this->os());
        $this->assertTrue($module->shouldPoll($this->os()));

        // a failure in an earlier poll is checked again
        $this->ipmiMethod->update(['last_check_successful' => false]);
        $this->assertTrue($module->shouldPoll($this->os()));

        // the IPMI check fails
        Process::fake(fn () => Process::result(exitCode: 1));
        $this->assertFalse($module->shouldPoll($this->os()));

        // no IPMI polling method
        $this->ipmiMethod->delete();
        $os = $this->os();

        $this->assertFalse($module->shouldDiscover($os));
        $this->assertFalse($module->shouldPoll($os));
    }

    public function testPollUsesTheSdrFromTheIpmiCheck(): void
    {
        $module = new Sensors;
        Sensor::factory()->create(['device_id' => $this->device->device_id, 'poller_type' => 'ipmi', 'sensor_class' => 'temperature', 'sensor_index' => '0', 'sensor_type' => 'ipmi', 'sensor_descr' => 'CPU Temp']);

        $os = $this->os();
        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, Mockery::mock(WriteInterface::class)->shouldIgnoreMissing());

        // the check reads the records, polling does not run ipmitool again
        Process::assertRanTimes(fn (PendingProcess $process) => in_array('sdr', (array) $process->command), 1);
        Process::assertDidntRun(fn (PendingProcess $process) => in_array('power', (array) $process->command));
        $this->assertEquals(47, $this->device->sensors()->where('sensor_descr', 'CPU Temp')->value('sensor_current'));
    }

    public function testAgentHddtempDiscoverAndPoll(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => "<<<hddtemp>>>\n|/dev/sda|WDC WD1003FBYX|35|C||/dev/sdb|ST4000|SLP|*|\n"]);
        $module = new Sensors;

        $module->discover($os);

        $sensors = Sensor::where('device_id', $os->getDeviceId())->orderBy('sensor_index')->get();
        $this->assertSame(['agent', 'agent'], $sensors->pluck('poller_type')->all());
        $this->assertSame(['/dev/sda: WDC WD1003FBYX', '/dev/sdb: ST4000'], $sensors->pluck('sensor_descr')->all());
        $this->assertEquals([35, null], $sensors->pluck('sensor_current')->all());

        // a sleeping disk is not polled
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => "<<<hddtemp>>>\n|/dev/sda|WDC WD1003FBYX|37|C||/dev/sdb|ST4000|SLP|*|\n"]);
        $device = DeviceCache::refresh($os->getDeviceId())->toArray();
        $os = OS::make($device);
        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->once()->withArgs(fn ($measurement, $fields, $tags) => $tags['index'] == 1 && $fields == ['sensor' => 37]);

        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $datastore);

        $this->assertEquals([37, null], Sensor::where('device_id', $os->getDeviceId())->orderBy('sensor_index')->pluck('sensor_current')->all());
    }

    public function testEachMethodSyncsOnlyItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'snmp', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'linux']);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'ipmi', 'sensor_class' => 'temperature', 'sensor_index' => '0', 'sensor_type' => 'ipmi']);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => "<<<hddtemp>>>\n|/dev/sda|WDC WD1003FBYX|35|C|\n"]);

        (new Sensors)->discover($os);

        // the device has no SNMP or IPMI method, their sensors are kept
        $this->assertSame(['agent', 'ipmi', 'snmp'], Sensor::where('device_id', $os->getDeviceId())->orderBy('poller_type')->pluck('poller_type')->all());
    }

    public function testUnavailableMethodKeepsItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'agent', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'hddtemp']);
        $this->fakeMethod(UnixAgentPollingMethod::class, false);

        $module = new Sensors;
        $this->assertFalse($module->shouldDiscover($os));
        $module->discover($os);

        $this->assertSame(1, Sensor::where('device_id', $os->getDeviceId())->count());
    }

    public function testAgentWithoutHddtempKeepsItsSensors(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        Sensor::factory()->create(['device_id' => $os->getDeviceId(), 'poller_type' => 'agent', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'hddtemp']);

        foreach (["<<<hddtemp>>>\n\n", "<<<df>>>\n/dev/sda1 100 50 50 50% /\n"] as $output) {
            $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $output]);
            (new Sensors)->discover($os);

            $this->assertSame(1, Sensor::where('device_id', $os->getDeviceId())->count());
        }
    }

    public function testRemovingAMethodDeletesOnlyItsSensors(): void
    {
        Sensor::factory()->create(['device_id' => $this->device->device_id, 'poller_type' => 'snmp', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'linux']);
        Sensor::factory()->create(['device_id' => $this->device->device_id, 'poller_type' => 'agent', 'sensor_class' => 'temperature', 'sensor_index' => '1', 'sensor_type' => 'hddtemp']);

        (new Sensors)->removeMethodData($this->device, PollingMethodType::UnixAgent);

        $this->assertSame(['snmp'], $this->device->sensors()->pluck('poller_type')->all());
    }

    private function os(): OS
    {
        // simulate a fresh discovery/poller run
        $device = DeviceCache::refresh($this->device->device_id)->toArray();

        return OS::make($device);
    }
}
