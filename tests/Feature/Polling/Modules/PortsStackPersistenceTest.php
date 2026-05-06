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

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\NetSnmpQuery;
use LibreNMS\Modules\PortsStack;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Mocks\SnmpQueryMock;

class PortsStackPersistenceTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(NetSnmpQuery::class, SnmpQueryMock::class);
    }

    private function discoverWithFixture(Device $device, string $fixture): void
    {
        $device->community = $fixture;
        $device->save();
        DeviceCache::flush();
        DeviceCache::setPrimary($device->device_id);
        $attrs = $device->fresh()->attributesToArray();
        $os = OS::make($attrs);
        (new PortsStack())->discover($os);
    }

    public function test_flag_off_preserves_current_behavior(): void
    {
        LibrenmsConfig::set('discovery.ports_stack.preserve_lagmib_stale_members', false);

        $device = Device::factory()->create(['community' => 'nxos_lag-mib', 'os' => 'nxos', 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);

        $this->discoverWithFixture($device, 'nxos_lag-mib');
        $initial = $device->portsStack()->count();
        $this->assertGreaterThan(0, $initial);

        $this->discoverWithFixture($device, 'nxos_lag-mib_member_dropped');
        $this->assertEquals($initial - 1, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
    }

    public function test_flag_on_preserves_dropped_member_as_notInService(): void
    {
        LibrenmsConfig::set('discovery.ports_stack.preserve_lagmib_stale_members', true);

        $device = Device::factory()->create(['community' => 'nxos_lag-mib', 'os' => 'nxos', 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);

        $this->discoverWithFixture($device, 'nxos_lag-mib');
        $initial = $device->portsStack()->count();

        $this->discoverWithFixture($device, 'nxos_lag-mib_member_dropped');
        $this->assertEquals($initial, $device->portsStack()->count());
        $this->assertEquals(1, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
    }

    public function test_flag_on_recovers_active_when_member_returns(): void
    {
        LibrenmsConfig::set('discovery.ports_stack.preserve_lagmib_stale_members', true);

        $device = Device::factory()->create(['community' => 'nxos_lag-mib', 'os' => 'nxos', 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);

        $this->discoverWithFixture($device, 'nxos_lag-mib');
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());

        $this->discoverWithFixture($device, 'nxos_lag-mib_member_dropped');
        $this->assertEquals(1, $device->portsStack()->where('ifStackStatus', 'notInService')->count());

        $this->discoverWithFixture($device, 'nxos_lag-mib');
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
    }

    public function test_flag_on_does_not_create_rows_for_never_seen_members(): void
    {
        LibrenmsConfig::set('discovery.ports_stack.preserve_lagmib_stale_members', true);

        $device = Device::factory()->create(['community' => 'nxos_lag-mib_member_dropped', 'os' => 'nxos', 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);
        $attrs = $device->attributesToArray();
        $os = OS::make($attrs);
        (new PortsStack())->discover($os);

        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
    }

    public function test_flag_on_does_not_apply_to_ifstacktable_branch(): void
    {
        LibrenmsConfig::set('discovery.ports_stack.preserve_lagmib_stale_members', true);

        $device = Device::factory()->create(['community' => 'arubaos-cx_10.06', 'os' => 'arubaos-cx', 'status' => 1]);
        DeviceCache::setPrimary($device->device_id);
        $attrs = $device->attributesToArray();
        $os = OS::make($attrs);
        $module = new PortsStack();

        $module->discover($os);
        $initial = $device->portsStack()->count();
        $this->assertGreaterThan(0, $initial);

        // force a row missing from the next walk
        $device->portsStack()->first()->delete();

        $module->discover($os);
        $this->assertEquals($initial, $device->portsStack()->count());
        $this->assertEquals(0, $device->portsStack()->where('ifStackStatus', 'notInService')->count());
    }
}
