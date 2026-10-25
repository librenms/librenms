<?php

namespace LibreNMS\Tests\Feature;

use App\Models\AlertOperationSegment;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\OspfArea;
use App\Models\Port;
use App\Models\PortVlan;
use App\Models\Sla;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Policies for read-only device, port and routing data that is shown on device pages.
 */
final class DeviceDataPolicyTest extends TestCase
{
    use RefreshDatabase;

    private Device $accessible;
    private Device $other;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('global-read');
        Role::findOrCreate('user');
        foreach (['device', 'diskio', 'port', 'routing'] as $prefix) {
            Permission::findOrCreate("$prefix.view");
            Permission::findOrCreate("$prefix.viewAll");
        }

        $this->accessible = Device::factory()->create();
        $this->other = Device::factory()->create();
    }

    /**
     * Every model with a policy in this group, with its global permission prefix
     *
     * @return array<string, array{class-string<Model>, string}>
     */
    public static function policyModels(): array
    {
        return [
            'AccessPoint' => [\App\Models\AccessPoint::class, 'device'],
            'Availability' => [\App\Models\Availability::class, 'device'],
            'CefSwitching' => [\App\Models\CefSwitching::class, 'device'],
            'DiskIo' => [\App\Models\DiskIo::class, 'diskio'],
            'IpsecTunnel' => [\App\Models\IpsecTunnel::class, 'device'],
            'Sla' => [\App\Models\Sla::class, 'device'],
            'Stp' => [\App\Models\Stp::class, 'device'],
            'MplsLsp' => [\App\Models\MplsLsp::class, 'routing'],
            'MplsLspPath' => [\App\Models\MplsLspPath::class, 'routing'],
            'MplsSap' => [\App\Models\MplsSap::class, 'routing'],
            'MplsSdp' => [\App\Models\MplsSdp::class, 'routing'],
            'MplsSdpBind' => [\App\Models\MplsSdpBind::class, 'routing'],
            'MplsService' => [\App\Models\MplsService::class, 'routing'],
            'MplsTunnelArHop' => [\App\Models\MplsTunnelArHop::class, 'routing'],
            'MplsTunnelCHop' => [\App\Models\MplsTunnelCHop::class, 'routing'],
            'OspfArea' => [\App\Models\OspfArea::class, 'routing'],
            'OspfInstance' => [\App\Models\OspfInstance::class, 'routing'],
            'OspfNbr' => [\App\Models\OspfNbr::class, 'routing'],
            'OspfPort' => [\App\Models\OspfPort::class, 'routing'],
            'Ospfv3Area' => [\App\Models\Ospfv3Area::class, 'routing'],
            'Ospfv3Instance' => [\App\Models\Ospfv3Instance::class, 'routing'],
            'Ospfv3Nbr' => [\App\Models\Ospfv3Nbr::class, 'routing'],
            'Ospfv3Port' => [\App\Models\Ospfv3Port::class, 'routing'],
            'Pseudowire' => [\App\Models\Pseudowire::class, 'routing'],
            'VrfLite' => [\App\Models\VrfLite::class, 'routing'],
            'Ipv4Address' => [\App\Models\Ipv4Address::class, 'port'],
            'Ipv4Mac' => [\App\Models\Ipv4Mac::class, 'port'],
            'Ipv4Network' => [\App\Models\Ipv4Network::class, 'port'],
            'Ipv6Address' => [\App\Models\Ipv6Address::class, 'port'],
            'Ipv6Nd' => [\App\Models\Ipv6Nd::class, 'port'],
            'Ipv6Network' => [\App\Models\Ipv6Network::class, 'port'],
            'PortAdsl' => [\App\Models\PortAdsl::class, 'port'],
            'PortSecurity' => [\App\Models\PortSecurity::class, 'port'],
            'PortsFdb' => [\App\Models\PortsFdb::class, 'port'],
            'PortStack' => [\App\Models\PortStack::class, 'port'],
            'PortStatistic' => [\App\Models\PortStatistic::class, 'port'],
            'PortStp' => [\App\Models\PortStp::class, 'port'],
            'PortVdsl' => [\App\Models\PortVdsl::class, 'port'],
            'PortVlan' => [\App\Models\PortVlan::class, 'port'],
            'Transceiver' => [\App\Models\Transceiver::class, 'port'],
        ];
    }

    /**
     * @param  class-string<Model>  $model
     */
    #[DataProvider('policyModels')]
    public function testViewAnyByRoleAndPermission(string $model, string $prefix): void
    {
        $this->assertNotNull(Gate::getPolicyFor($model));

        $this->assertTrue(Gate::forUser($this->user('user'))->allows('viewAny', $model), 'user role');
        $this->assertTrue(Gate::forUser($this->user('global-read'))->allows('viewAny', $model), 'global-read role');
        $this->assertFalse(Gate::forUser($this->user())->allows('viewAny', $model), 'no role');
        $this->assertTrue(Gate::forUser($this->user(null, "$prefix.view"))->allows('viewAny', $model), "$prefix.view");
        $this->assertTrue(Gate::forUser($this->user(null, "$prefix.viewAll"))->allows('viewAll', $model), "$prefix.viewAll");
    }

    public function testDeviceScopedViewRequiresDeviceAccess(): void
    {
        $user = $this->user('user');
        $user->devicesOwned()->attach($this->accessible->device_id);

        $this->assertTrue(Gate::forUser($user)->allows('view', $this->onDevice(Sla::class, $this->accessible)));
        $this->assertFalse(Gate::forUser($user)->allows('view', $this->onDevice(Sla::class, $this->other)));
        $this->assertTrue(Gate::forUser($user)->allows('view', $this->onDevice(OspfArea::class, $this->accessible)));
        $this->assertFalse(Gate::forUser($user)->allows('view', $this->onDevice(OspfArea::class, $this->other)));
    }

    public function testViewAllSeesEveryDevice(): void
    {
        $user = $this->user(null, 'device.viewAll');

        $this->assertTrue(Gate::forUser($user)->allows('view', $this->onDevice(Sla::class, $this->other)));
    }

    public function testPortScopedViewByDeviceOrPortAccess(): void
    {
        $accessiblePort = Port::factory()->create(['device_id' => $this->accessible->device_id]);
        $otherPort = Port::factory()->create(['device_id' => $this->other->device_id]);
        $ownedPort = Port::factory()->create(['device_id' => $this->other->device_id]);

        $user = $this->user('user');
        $user->devicesOwned()->attach($this->accessible->device_id);
        $user->portsOwned()->attach($ownedPort->port_id);
        $gate = Gate::forUser($user);

        // no device_id column, device comes from the port
        $this->assertTrue($gate->allows('view', Ipv4Address::factory()->make(['port_id' => $accessiblePort->port_id])));
        $this->assertTrue($gate->allows('view', Ipv4Address::factory()->make(['port_id' => $ownedPort->port_id])));
        $this->assertFalse($gate->allows('view', Ipv4Address::factory()->make(['port_id' => $otherPort->port_id])));

        $vlan = fn (Port $port) => (new PortVlan)->forceFill(['device_id' => $port->device_id, 'port_id' => $port->port_id]);
        $this->assertTrue($gate->allows('view', $vlan($accessiblePort)));
        $this->assertTrue($gate->allows('view', $vlan($ownedPort)));
        $this->assertFalse($gate->allows('view', $vlan($otherPort)));
    }

    public function testNetworkViewRequiresAccessToAConnectedPort(): void
    {
        $accessiblePort = Port::factory()->create(['device_id' => $this->accessible->device_id]);
        $otherPort = Port::factory()->create(['device_id' => $this->other->device_id]);
        $visible = Ipv4Network::factory()->create();
        $hidden = Ipv4Network::factory()->create();
        Ipv4Address::factory()->create(['port_id' => $accessiblePort->port_id, 'ipv4_network_id' => $visible->ipv4_network_id]);
        Ipv4Address::factory()->create(['port_id' => $otherPort->port_id, 'ipv4_network_id' => $hidden->ipv4_network_id]);

        $user = $this->user('user');
        $user->devicesOwned()->attach($this->accessible->device_id);

        $this->assertTrue(Gate::forUser($user)->allows('view', $visible));
        $this->assertFalse(Gate::forUser($user)->allows('view', $hidden));
    }

    public function testAlertOperationSegmentMatchesAlertOperation(): void
    {
        Permission::findOrCreate('alert-rule.view');
        $user = $this->user('user');

        $this->assertSame(
            Gate::forUser($user)->allows('viewAny', \App\Models\AlertOperation::class),
            Gate::forUser($user)->allows('viewAny', AlertOperationSegment::class),
        );
        $this->assertTrue(Gate::forUser($user)->allows('viewAny', AlertOperationSegment::class));
        $this->assertFalse(Gate::forUser($user)->allows('view', new AlertOperationSegment));
        $this->assertTrue(Gate::forUser($this->user(null, 'alert-rule.view'))->allows('view', new AlertOperationSegment));
    }

    private function user(?string $role = null, ?string $permission = null): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        if ($role) {
            $user->assignRole($role);
        }
        if ($permission) {
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @return T
     */
    private function onDevice(string $model, Device $device): Model
    {
        return (new $model)->forceFill(['device_id' => $device->device_id]);
    }
}
