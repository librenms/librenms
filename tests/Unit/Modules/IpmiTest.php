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
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Ipmi;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;

final class IpmiTest extends InMemoryDbTestCase
{
    private const SENSOR_OUTPUT = <<<'EOT'
FAN1             | 3600.000   | RPM        | ok    | 300.000   | 500.000   | 700.000   | 25300.000 | 25400.000 | 25500.000
CPU Temp         | 45.000     | degrees C  | ok    | na        | na        | na        | 85.000    | 90.000    | 95.000
12V              | na         | Volts      | na    | na        | na        | na        | na        | na        | na
PS Status        | 0x1        | discrete   | 0x0100| na        | na        | na        | na        | na        | na
EOT;

    private const SDR_OUTPUT = <<<'EOT'
CPU Temp,47,degrees C,ok,
FAN1,E1h,RPM,ok,
PS Status,0x01,discrete,ok,
EOT;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'linux']);
        $this->device->setAttrib('ipmi_hostname', 'bmc.example.com');
        $this->device->setAttrib('ipmi_type', 'lanplus');
        DeviceCache::setPrimary($this->device->device_id);

        Process::fake(fn (PendingProcess $process) => in_array('sdr', (array) $process->command)
            ? self::SDR_OUTPUT
            : self::SENSOR_OUTPUT);
    }

    public function testDiscoverAndPoll(): void
    {
        $module = new Ipmi;
        $module->discover($this->os());

        $sensors = $this->device->sensors()->orderBy('sensor_index')->get();
        $this->assertSame(['CPU Temp', 'FAN1'], $sensors->pluck('sensor_descr')->all());
        $this->assertSame(['temperature', 'fanspeed'], $sensors->pluck('sensor_class')->all());
        $this->assertEquals(45, $sensors[0]->sensor_current);
        $this->assertEquals(90, $sensors[0]->sensor_limit);
        $this->assertEquals(500, $sensors[1]->sensor_limit_low);
        $this->assertTrue($module->dataExists($this->device));

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'ipmi'
            && $tags['rrd_name'] == ['sensor', 'temperature', 'ipmi', 'CPU Temp'] && $fields == ['sensor' => 47]);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'ipmi'
            && $tags['rrd_name'] == ['sensor', 'fanspeed', 'ipmi', 'FAN1'] && $fields == ['sensor' => 225]);

        $module->poll($this->os(), $datastore);

        $this->assertEquals([47, 225], $this->device->sensors()->orderBy('sensor_index')->pluck('sensor_current')->all());
    }

    public function testDiscoveryRemovesSensorsWhenIpmiDisabled(): void
    {
        $module = new Ipmi;
        $module->discover($this->os());
        $this->assertSame(2, $this->device->sensors()->count());

        $this->device->forgetAttrib('ipmi_hostname');
        $module->discover($this->os());

        $this->assertSame(0, $this->device->sensors()->count());
        $this->assertFalse($module->dataExists($this->device));
    }

    private function os(): OS
    {
        // simulate a fresh discovery/poller run
        $device = DeviceCache::refresh($this->device->device_id)->toArray();

        return OS::make($device);
    }
}
