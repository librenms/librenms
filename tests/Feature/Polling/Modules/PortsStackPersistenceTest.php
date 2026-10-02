<?php

/**
 * PortsStackPersistenceTest.php
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
 * @copyright  2026 Josh Thomas-Ward
 * @author     Josh Thomas-Ward <josh.thomasward@pelagicai.com>
 */

namespace LibreNMS\Tests\Feature\Polling\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\PortsStack;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Mocks\SnmprecSnmpBackend;

class PortsStackPersistenceTest extends DBTestCase
{
    use DatabaseTransactions;

    // nxos_lag-mib_member_dropped.snmprec is nxos_lag-mib.snmprec minus this member of aggregator 369099075
    private const DROPPED_MEMBER = 940683264;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(SnmpBackendInterface::class, SnmprecSnmpBackend::class);
    }

    public function test_poll_keeps_dropped_member_as_notInService(): void
    {
        $device = $this->makeDevice('nxos', 'nxos_lag-mib');
        $this->discover($device, 'nxos_lag-mib');
        $initial = $device->portsStack()->count();
        $this->assertGreaterThan(0, $initial);

        $this->poll($device, 'nxos_lag-mib_member_dropped');

        $this->assertEquals($initial, $device->portsStack()->count());
        $preserved = $device->portsStack()->where('ifStackStatus', 'notInService')->get();
        $this->assertCount(1, $preserved);
        $this->assertEquals(self::DROPPED_MEMBER, $preserved->first()->low_ifIndex);
        $this->assertEvents($device, ['dropped out of']);
    }

    public function test_repeated_polls_log_the_drop_once(): void
    {
        $device = $this->makeDevice('nxos', 'nxos_lag-mib');
        $this->discover($device, 'nxos_lag-mib');

        $this->poll($device, 'nxos_lag-mib_member_dropped');
        $this->poll($device, 'nxos_lag-mib_member_dropped');

        $this->assertEquals(1, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
        $this->assertEvents($device, ['dropped out of']);
    }

    public function test_poll_flips_returning_member_back_to_active(): void
    {
        $device = $this->makeDevice('nxos', 'nxos_lag-mib');
        $this->discover($device, 'nxos_lag-mib');
        $initial = $device->portsStack()->count();

        $this->poll($device, 'nxos_lag-mib_member_dropped');
        $this->poll($device, 'nxos_lag-mib');

        $this->assertEquals($initial, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
        $this->assertEvents($device, ['dropped out of', 'rejoined']);
    }

    public function test_discovery_removes_member_still_missing_and_logs_it(): void
    {
        $device = $this->makeDevice('nxos', 'nxos_lag-mib');
        $this->discover($device, 'nxos_lag-mib');
        $initial = $device->portsStack()->count();

        $this->poll($device, 'nxos_lag-mib_member_dropped');
        $this->discover($device, 'nxos_lag-mib_member_dropped');

        $this->assertEquals($initial - 1, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
        $this->assertEquals(0, $device->portsStack()->where('low_ifIndex', self::DROPPED_MEMBER)->count());
        $this->assertEvents($device, ['dropped out of', 'at discovery, removed']);
    }

    public function test_first_discovery_does_not_create_notInService_rows(): void
    {
        $device = $this->makeDevice('nxos', 'nxos_lag-mib_member_dropped');

        $this->discover($device, 'nxos_lag-mib_member_dropped');

        $this->assertGreaterThan(0, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
        $this->assertEvents($device, []);
    }

    public function test_ifstacktable_branch_does_not_preserve_rows(): void
    {
        $device = $this->makeDevice('arubaos-cx', 'arubaos-cx_10.06');
        $this->discover($device, 'arubaos-cx_10.06');
        $initial = $device->portsStack()->count();
        $this->assertGreaterThan(0, $initial);

        // Delete a row, then poll. ifStackTable still reports it, so it comes back as active.
        // Preservation is scoped to the LAG-MIB branch and must not touch this path.
        $device->portsStack()->first()->delete();
        $this->poll($device, 'arubaos-cx_10.06');

        $this->assertEquals($initial, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
        $this->assertEvents($device, []);
    }

    private function makeDevice(string $os, string $fixture): Device
    {
        $device = Device::factory()->create(['community' => $fixture, 'os' => $os, 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);

        return $device;
    }

    private function discover(Device $device, string $fixture): void
    {
        (new PortsStack)->discover($this->osFor($device, $fixture));
    }

    private function poll(Device $device, string $fixture): void
    {
        (new PortsStack)->poll($this->osFor($device, $fixture), $this->createMock(DataStorageInterface::class));
    }

    private function osFor(Device $device, string $fixture): OS
    {
        $device->community = $fixture;
        $device->save();
        DeviceCache::flush();
        DeviceCache::setPrimary($device->device_id);

        $attributes = $device->fresh()->attributesToArray();

        return OS::make($attributes);
    }

    /**
     * @param  string[]  $expected  substring of each interface eventlog message, in order
     */
    private function assertEvents(Device $device, array $expected): void
    {
        $messages = Eventlog::where('device_id', $device->device_id)
            ->where('type', 'interface')
            ->orderBy('event_id')
            ->pluck('message')
            ->all();

        $this->assertCount(count($expected), $messages, implode("\n", $messages));
        foreach ($expected as $i => $fragment) {
            $this->assertStringContainsString($fragment, $messages[$i]);
        }
    }
}
