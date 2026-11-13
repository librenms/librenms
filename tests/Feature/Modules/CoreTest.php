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
use LibreNMS\Polling\Method\Methods\ProxmoxApiPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\PerDeviceMethodResults;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class CoreTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    /**
     * @param  list<PollingMethodType>  $methods
     */
    #[DataProvider('osDetectionProvider')]
    public function testDiscoveryDetectsTheOs(array $methods, ?string $sysDescr, ?string $sysObjectID, string $agentOs, bool $discovers, string $expected): void
    {
        $device = Device::factory()->create(['os' => 'ping', 'type' => '']);
        foreach ($methods as $type) {
            DevicePollingMethod::factory()->for($device)->create(['method_type' => $type, 'affects_availability' => false]);
        }
        $this->fakeSnmpSystem($sysDescr, $sysObjectID);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentOsOutput($agentOs)]);
        $this->fakeMethod(ProxmoxApiPollingMethod::class, true, ['cluster_id' => 'test']);

        $os = $this->os($device);
        $os->setMethodResults(new PerDeviceMethodResults($os->getDevice()));
        $module = new Core;

        $this->assertSame($discovers, $module->shouldDiscover($os));
        if ($discovers) {
            $module->discover($os);
        }

        $this->assertSame($expected, $os->getDevice()->os);
        if ($expected !== 'ping') {
            $this->assertSame('server', $os->getDevice()->type);
        }
    }

    /**
     * @return array<string, array{list<PollingMethodType>, ?string, ?string, string, bool, string}>
     */
    public static function osDetectionProvider(): array
    {
        $agent = [PollingMethodType::Icmp, PollingMethodType::UnixAgent];
        $both = [PollingMethodType::Snmp, PollingMethodType::UnixAgent];
        $freebsd = ['FreeBSD host 14.1-RELEASE', '.1.3.6.1.4.1.8072.3.2.8'];
        $unknown = ['Some device', '.1.3.6.1.4.1.99999.1'];
        $linux = ['Linux host 6.1.0', '.1.3.6.1.4.1.8072.3.2.10'];
        $windows = ['Hardware: Intel64 Family 6 - Software: Windows Version 6.3 (Build 20348 Multiprocessor Free)', '.1.3.6.1.4.1.311.1.1.3.1.2'];

        return [
            'agent only' => [$agent, null, null, 'linux', true, 'linux'],
            'agent only, unknown agent os' => [$agent, null, null, 'windows', true, 'ping'],
            'icmp only' => [[PollingMethodType::Icmp], null, null, 'linux', false, 'ping'],
            'snmp identifies the device' => [$both, ...$freebsd, 'linux', true, 'freebsd'],
            'snmp finds generic' => [$both, ...$unknown, 'linux', true, 'linux'],
            'snmp finds generic, unknown agent os' => [$both, ...$unknown, 'windows', true, 'generic'],
            'the more specific os wins, in any order' => [[PollingMethodType::UnixAgent, PollingMethodType::ProxmoxApi], null, null, 'linux', true, 'proxmox'],
            'snmp finds a generic os, another method a specific one' => [[PollingMethodType::Snmp, PollingMethodType::ProxmoxApi], ...$linux, 'linux', true, 'proxmox'],
            'snmp decides between two specific answers' => [[PollingMethodType::Snmp, PollingMethodType::ProxmoxApi], ...$windows, 'linux', true, 'windows'],
            'snmp down' => [$both, null, null, 'linux', false, 'ping'],
        ];
    }

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
        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $datastore);

        $this->assertEquals(2.5, $device->stats()->value('ping_rtt_last'));
        $this->assertTrue(Eventlog::where('device_id', $device->device_id)->where('message', 'like', 'Duplicate ICMP response%')->exists());
    }

    public function testDoesNotPollWithoutSnmpOrIcmp(): void
    {
        $device = Device::factory()->create(['os' => 'ping']);
        $os = $this->os($device);

        $this->assertFalse((new Core)->shouldPoll($os));
    }

    private function os(Device $device): OS
    {
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        return OS::make($deviceArray);
    }
}
