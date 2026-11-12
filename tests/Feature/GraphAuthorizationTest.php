<?php

/**
 * GraphAuthorizationTest.php
 *
 * Verifies legacy graph auth includes only authorize devices the user may access.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Location;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class GraphAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Device $accessible;
    private Device $other;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user');

        $this->location = Location::factory()->create();
        $this->accessible = Device::factory()->create(['location_id' => $this->location->id]);
        $this->other = Device::factory()->create(['location_id' => $this->location->id]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->accessible->device_id);
        $this->actingAs($user);
    }

    /**
     * Run a graph type's auth include in an isolated scope.
     *
     * @param  array<string, mixed>  $vars
     * @return array{auth: bool, devices: array<int, array<string, mixed>>}
     */
    private function runAuth(string $type, array $vars, bool $auth = false): array
    {
        $devices = [];
        require __DIR__ . "/../../includes/html/graphs/$type/auth.inc.php";

        return ['auth' => $auth, 'devices' => $devices];
    }

    public function testLocationGraphOnlyIncludesPermittedDevices(): void
    {
        $result = $this->runAuth('location', ['id' => $this->location->id]);

        $this->assertTrue($result['auth']);
        $this->assertEquals([$this->accessible->device_id], array_column($result['devices'], 'device_id'));
    }

    public function testLocationGraphGuestAuthIncludesAllDevices(): void
    {
        $result = $this->runAuth('location', ['id' => $this->location->id], true);

        $this->assertTrue($result['auth']);
        $this->assertEqualsCanonicalizing(
            [$this->accessible->device_id, $this->other->device_id],
            array_column($result['devices'], 'device_id')
        );
    }

    public function testLocationGraphDeniedWithoutPermittedDevices(): void
    {
        $otherLocation = Location::factory()->create();
        Device::factory()->create(['location_id' => $otherLocation->id]);

        $result = $this->runAuth('location', ['id' => $otherLocation->id]);

        $this->assertFalse($result['auth']);
        $this->assertEmpty($result['devices']);
    }

    public function testSmokepingGraphRequiresPermissionOnGraphedDevice(): void
    {
        $this->assertTrue($this->runAuth('smokeping', ['device' => $this->accessible->device_id, 'src' => $this->accessible->device_id])['auth']);
        $this->assertFalse($this->runAuth('smokeping', ['device' => $this->other->device_id, 'src' => $this->accessible->device_id])['auth']);
    }

    public function testSmokepingGraphRequiresPermissionOnPeerDevice(): void
    {
        $this->assertFalse($this->runAuth('smokeping', ['device' => $this->accessible->device_id, 'src' => $this->other->device_id])['auth']);
        $this->assertFalse($this->runAuth('smokeping', ['device' => $this->accessible->device_id, 'dest' => $this->other->device_id])['auth']);
        $this->assertTrue($this->runAuth('smokeping', ['device' => $this->accessible->device_id, 'dest' => $this->accessible->device_id])['auth']);
    }

    public function testCustomerGraphOnlyIncludesPermittedPorts(): void
    {
        $accessiblePort = Port::factory()->for($this->accessible)->create(['port_descr_type' => 'cust', 'port_descr_descr' => 'Acme']);
        Port::factory()->for($this->other)->create(['port_descr_type' => 'cust', 'port_descr_descr' => 'Acme']);

        foreach ([['customer' => 'Acme'], ['id' => 'Acme']] as $vars) {
            $result = $this->runCustomerAuth($vars);

            $this->assertTrue($result['auth']);
            $this->assertEquals([$accessiblePort->port_id], $result['ports']);
        }
    }

    public function testCustomerGraphGuestAuthIncludesAllPorts(): void
    {
        $accessiblePort = Port::factory()->for($this->accessible)->create(['port_descr_type' => 'cust', 'port_descr_descr' => 'Acme']);
        $otherPort = Port::factory()->for($this->other)->create(['port_descr_type' => 'cust', 'port_descr_descr' => 'Acme']);

        $result = $this->runCustomerAuth(['customer' => 'Acme'], true);

        $this->assertTrue($result['auth']);
        $this->assertEqualsCanonicalizing([$accessiblePort->port_id, $otherPort->port_id], $result['ports']);
    }

    public function testCustomerGraphDeniedWithoutPermittedPorts(): void
    {
        Port::factory()->for($this->other)->create(['port_descr_type' => 'cust', 'port_descr_descr' => 'Acme']);
        Port::factory()->for($this->accessible)->create(['port_descr_type' => 'core', 'port_descr_descr' => 'Acme']);

        $this->assertFalse($this->runCustomerAuth(['customer' => 'Acme'])['auth']);
        $this->assertFalse($this->runCustomerAuth(['customer' => 'Nobody'])['auth']);
    }

    /**
     * @param  array<string, mixed>  $vars
     * @return array{auth: bool, ports: list<int>}
     */
    private function runCustomerAuth(array $vars, bool $auth = false): array
    {
        require __DIR__ . '/../../includes/html/graphs/customer/auth.inc.php';

        /** @var Collection<Port> $customer_ports */

        return ['auth' => $auth, 'ports' => $customer_ports->pluck('port_id')->all()];
    }
}
