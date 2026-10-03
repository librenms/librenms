<?php

/**
 * IpmiTest.php
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

namespace LibreNMS\Tests\Unit\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Sensor;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Process;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Ipmi;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;

final class IpmiTest extends InMemoryDbTestCase
{
    private const SENSOR_OUTPUT = <<<'EOT'
FAN1             | 3600.000   | RPM        | ok    | 300.000   | 500.000   | 700.000   | 25300.000 | 25400.000 | 25500.000
CPU Temp         | 45.000     | degrees C  | ok    | na        | na        | na        | 85.000    | 90.000    | 95.000
Chassis Intru    | 0x0        | discrete   | 0x0080| na        | na        | na        | na        | na        | na
12V              | na         | Volts      | na    | na        | na        | na        | na        | na        | na
VCORE            | 1.310      | Volts      | ok    | na        | 0.680     | 0.700     | 1.450     | 1.520     | na
EOT;

    private const SDR_OUTPUT = <<<'EOT'
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

    public function testDiscoverAndPoll(): void
    {
        $module = new Ipmi;
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

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'ipmi'
            && $tags['rrd_name'] == ['sensor', 'temperature', 'ipmi', 'CPU Temp'] && $fields == ['sensor' => 47]);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'ipmi'
            && $tags['rrd_name'] == ['sensor', 'fanspeed', 'ipmi', 'FAN1'] && $fields == ['sensor' => 225]);

        $module->poll($this->os(), $datastore);

        $this->assertEquals([47, 225, 1.31], $this->device->sensors()->orderBy('sensor_index')->pluck('sensor_current')->all());
    }

    public function testPortAndTimeoutAreLeftToIpmitoolUnlessSet(): void
    {
        (new Ipmi)->discover($this->os());

        Process::assertRan(fn (PendingProcess $process) => ! in_array('-p', (array) $process->command) && ! in_array('-N', (array) $process->command));

        $this->ipmiMethod->update(['settings' => ['hostname' => 'bmc.example.com', 'type' => 'lanplus', 'port' => 6230, 'timeout' => 5]]);
        (new Ipmi)->discover($this->os());

        Process::assertRan(function (PendingProcess $process): bool {
            $command = implode(' ', (array) $process->command);

            return str_contains($command, '-p 6230') && str_contains($command, '-N 5');
        });
    }

    public function testRediscoveryDoesNotUpdateUnchangedSensors(): void
    {
        $module = new Ipmi;
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

    public function testDiscoverHandlesEmptyAndShortOutput(): void
    {
        Process::fake(fn () => "\nCPU Temp | 45.000 | degrees C | ok\n\n");

        (new Ipmi)->discover($this->os());

        $sensor = $this->device->sensors()->sole();
        $this->assertSame('CPU Temp', $sensor->sensor_descr);
        $this->assertEquals(45, $sensor->sensor_current);
        $this->assertNull($sensor->sensor_limit_warn);

        Process::fake(fn () => '');
        (new Ipmi)->discover($this->os());

        $this->assertSame(0, $this->device->sensors()->count());
    }

    public function testDoesNotRunWithoutIpmiConfigured(): void
    {
        $module = new Ipmi;
        $status = new ModuleStatus(true);

        $this->assertTrue($module->shouldDiscover($this->os(), $status, new ConnectivityHelper($this->os()->getDevice())));
        $this->assertTrue($module->shouldPoll($this->os(), $status, new ConnectivityHelper($this->os()->getDevice())));

        $this->assertFalse($module->shouldDiscover($this->os(), new ModuleStatus(false), new ConnectivityHelper($this->os()->getDevice())));

        // the last IPMI check failed
        $this->ipmiMethod->update(['last_check_successful' => false]);
        $os = $this->os();
        $this->assertFalse($module->shouldPoll($os, $status, new ConnectivityHelper($os->getDevice())));

        // no IPMI polling method
        $this->ipmiMethod->delete();
        $os = $this->os();
        $connectivity = new ConnectivityHelper($os->getDevice());

        $this->assertFalse($module->shouldDiscover($os, $status, $connectivity));
        $this->assertFalse($module->shouldPoll($os, $status, $connectivity));
    }

    private function os(): OS
    {
        // simulate a fresh discovery/poller run
        $device = DeviceCache::refresh($this->device->device_id)->toArray();

        return OS::make($device);
    }
}
