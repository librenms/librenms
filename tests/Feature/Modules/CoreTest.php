<?php

/**
 * CoreTest.php
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
use App\Models\Eventlog;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Enum\FpingExitCode;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Core;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;

final class CoreTest extends InMemoryDbTestCase
{
    public function testPingOnlyDeviceStoresTheIcmpResponse(): void
    {
        $device = Device::factory()->create(['os' => 'ping']);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'affects_availability' => false, // checked by the first module that needs it
        ]);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->once()->andReturn(new FpingResponse(3, 3, 0, 1.5, 3.5, 2.5, 1, FpingExitCode::Success));
        $this->app->instance(Fping::class, $fping);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'icmp-perf' && $fields['avg'] == 2.5);
        $this->app->instance('Datastore', $datastore);

        $os = $this->os($device);
        $module = new Core;
        $this->assertTrue($module->shouldPoll($os, new ModuleStatus(true), new ConnectivityHelper($os->getDevice(), $os->getMethodResults())));
        $module->poll($os, $datastore);

        $this->assertEquals(2.5, $device->stats()->value('ping_rtt_last'));
        $this->assertTrue(Eventlog::where('device_id', $device->device_id)->where('message', 'like', 'Duplicate ICMP response%')->exists());
    }

    public function testDoesNotPollWithoutSnmpOrIcmp(): void
    {
        $device = Device::factory()->create(['os' => 'ping']);
        $os = $this->os($device);

        $this->assertFalse((new Core)->shouldPoll($os, new ModuleStatus(true), new ConnectivityHelper($os->getDevice(), $os->getMethodResults())));
    }

    private function os(Device $device): OS
    {
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        return OS::make($deviceArray);
    }
}
