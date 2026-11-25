<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;
use App\Models\Ospfv3Area;
use App\Models\Ospfv3Instance;
use App\Models\Ospfv3Nbr;
use App\Models\Ospfv3Port;
use App\Models\Port;
use App\Models\Route;
use App\Models\User;
use App\Models\Vrf;
use App\Http\Middleware\CheckInstalled;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Exceptions\SnmpConnectionException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\ArpTableDiscovery;
use LibreNMS\Interfaces\Discovery\Ipv6NdDiscovery;
use LibreNMS\Modules\ArpTable;
use LibreNMS\Modules\BgpPeers;
use LibreNMS\Modules\Ipv6Nd as Ipv6NdModule;
use LibreNMS\Modules\Ospfv3;
use LibreNMS\Modules\Vrf as VrfModule;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Util\VrfContextCleanup;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;

final class VrfContextCleanupTest extends InMemoryDbTestCase
{
    /**
     * @return array<string, array{string, class-string<Model>}>
     */
    public static function relationships(): array
    {
        return [
            'arp' => ['macs', Ipv4Mac::class],
            'nd' => ['nd', Ipv6Nd::class],
            'ipv4' => ['ipv4', Ipv4Address::class],
            'ipv6' => ['ipv6', Ipv6Address::class],
            'bgp' => ['bgppeers', BgpPeer::class],
            'bgp families' => ['bgpPeersCbgp', BgpPeerCbgp::class],
            'ospf instances' => ['ospfInstances', OspfInstance::class],
            'ospf areas' => ['ospfAreas', OspfArea::class],
            'ospf ports' => ['ospfPorts', OspfPort::class],
            'ospf neighbors' => ['ospfNbrs', OspfNbr::class],
            'ospfv3 instances' => ['ospfv3Instances', Ospfv3Instance::class],
            'ospfv3 areas' => ['ospfv3Areas', Ospfv3Area::class],
            'ospfv3 ports' => ['ospfv3Ports', Ospfv3Port::class],
            'ospfv3 neighbors' => ['ospfv3Nbrs', Ospfv3Nbr::class],
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    #[DataProvider('relationships')]
    public function testOnlySelectedContextAndDeviceAreCleaned(string $relationship, string $modelClass): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        $otherPort = Port::factory()->for($other)->create();
        Vrf::factory()->for($device)->create(['context_name' => 'active', 'context_enabled' => true, 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['context_name' => 'disabled', 'context_enabled' => false, 'context_verified' => true]);

        foreach (['', 'active', 'disabled', 'failed', 'removed', 'DISABLED'] as $context) {
            $this->createRow($modelClass, $device, $port, $context);
        }
        $this->createRow($modelClass, $other, $otherPort, 'disabled');
        if (in_array($relationship, ['macs', 'nd'], true)) {
            $this->createRow($modelClass, $device, $port, null);
        }

        $names = array_column(VrfContextCleanup::candidates($device), 'name');
        $this->assertEqualsCanonicalizing(['disabled', 'failed', 'removed', 'DISABLED'], $names);
        VrfContextCleanup::clean($device, 'disabled');

        $remaining = $device->$relationship()->get()->pluck('context_name')->all();
        $expected = ['', 'active', 'failed', 'removed', 'DISABLED'];
        if (in_array($relationship, ['macs', 'nd'], true)) {
            $expected[] = null;
        }
        $this->assertEqualsCanonicalizing($expected, $remaining);
        $this->assertSame(1, $other->$relationship()->count());
    }

    public function testCleanupPageAndActionProtectActiveAndDefaultContexts(): void
    {
        $this->withoutMiddleware(CheckInstalled::class);
        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        Vrf::factory()->for($device)->create(['vrf_name' => 'active', 'context_name' => 'active', 'context_enabled' => true, 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'shared-disabled', 'context_name' => 'shared', 'context_enabled' => false, 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'shared-active', 'context_name' => 'shared', 'context_enabled' => true, 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'disabled', 'context_name' => 'disabled', 'context_enabled' => false, 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'failed', 'context_name' => 'failed', 'context_enabled' => true, 'context_verified' => false]);
        foreach (['', 'active', 'shared', 'disabled', 'failed', 'removed'] as $context) {
            BgpPeer::factory()->for($device)->create(['context_name' => $context]);
        }
        BgpPeer::factory()->for($other)->create(['context_name' => 'removed']);
        Route::factory()->for($device)->create(['context_name' => 'removed', 'port_id' => 0]);
        $url = route('device.edit.routing.context.cleanup', $device);

        $candidates = $this->actingAs($admin)->get(route('device.edit.routing', $device))
            ->assertOk()->assertSee('Clean up data')->assertSee('Removed SNMP contexts')->viewData('cleanup_contexts');
        $this->assertSame(['disabled', 'failed', 'removed'], array_column($candidates, 'name'));
        $this->actingAs($user)->postJson($url, ['context' => base64_encode('removed')])->assertForbidden();
        foreach (['', 'active', 'shared'] as $context) {
            $this->actingAs($admin)->postJson($url, ['context' => base64_encode($context)])->assertUnprocessable();
        }
        $this->actingAs($admin)->postJson($url, ['context' => 'invalid!'])->assertUnprocessable();
        $this->actingAs($admin)->postJson($url, ['context' => base64_encode('no-data')])->assertUnprocessable();
        $this->actingAs($admin)->postJson($url, ['context' => base64_encode('disabled')])->assertOk();
        $this->actingAs($admin)->postJson($url, ['context' => base64_encode('failed')])->assertOk();
        $this->actingAs($admin)->postJson($url, ['context' => base64_encode('removed')])->assertOk();
        $this->assertSame(0, $device->bgppeers()->where('context_name', 'removed')->count());
        $this->assertSame(1, $other->bgppeers()->where('context_name', 'removed')->count());
        $this->assertSame('removed', $device->routes()->sole()->context_name);
        $this->assertSame(1, $device->bgppeers()->where('context_name', 'active')->count());
    }

    public function testNeighborModulesKeepInactiveRowsDuringDiscoveryAndPolling(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Vrf::factory()->for($device)->create(['context_name' => 'disabled', 'context_enabled' => false]);
        $this->createRow(Ipv4Mac::class, $device, $port, 'disabled');
        $this->createRow(Ipv6Nd::class, $device, $port, 'disabled');
        $os = Mockery::mock(OS::class, ArpTableDiscovery::class, Ipv6NdDiscovery::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverArpTable', 'discoverIpv6Neighbor')->andReturn(collect());
        $datastore = Mockery::mock(DataStorageInterface::class);

        foreach ([new ArpTable, new Ipv6NdModule] as $module) {
            $module->discover($os);
            $module->poll($os, $datastore);
        }

        $this->assertSame(1, $device->macs()->count());
        $this->assertSame(1, $device->nd()->count());
    }

    public function testChangingContextSettingsUpdatesCleanupCandidates(): void
    {
        $this->withoutMiddleware(CheckInstalled::class);
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $device = Device::factory()->create();
        $vrf = Vrf::factory()->for($device)->create([
            'vrf_name' => 'blue', 'context_name' => 'blue', 'context_enabled' => true, 'context_verified' => true,
        ]);
        BgpPeer::factory()->for($device)->create(['context_name' => 'blue']);
        $url = route('device.edit.routing.vrf.update', [$device, $vrf]);

        $this->actingAs($admin)->patchJson($url, ['context_enabled' => false])
            ->assertOk()->assertJsonPath('cleanup_contexts.0.name', 'blue');
        $this->actingAs($admin)->patchJson($url, ['context_enabled' => true])
            ->assertOk()->assertJsonCount(0, 'cleanup_contexts');
        $this->actingAs($admin)->postJson(route('device.edit.routing.context.cleanup', $device), ['context' => base64_encode('blue')])
            ->assertUnprocessable();
        $this->assertSame(1, $device->bgppeers()->count());
    }

    public function testBgpAndOspfv3EarlyReturnsKeepInactiveRows(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        $this->createRow(BgpPeer::class, $device, $port, 'removed');
        $this->createRow(BgpPeerCbgp::class, $device, $port, 'removed');
        $this->createRow(Ospfv3Instance::class, $device, $port, 'removed');
        $this->createRow(Ospfv3Area::class, $device, $port, 'removed');
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverBgpPeers')->andThrow(new SnmpConnectionException('Timeout'));
        $response = Mockery::mock(SnmpResponse::class);
        $response->shouldReceive('valuesByIndex')->andReturn([]);
        $snmp = Mockery::mock(SnmpQueryBuilder::class);
        $snmp->shouldReceive('context')->with('')->andReturnSelf();
        $snmp->shouldReceive('hideMib', 'enumStrings')->andReturnSelf();
        $snmp->shouldReceive('walk')->with('OSPFV3-MIB::ospfv3GeneralGroup')->andReturn($response);
        $this->app->instance(SnmpQueryBuilder::class, $snmp);
        $datastore = Mockery::mock(DataStorageInterface::class);

        (new BgpPeers)->discover($os);
        (new BgpPeers)->poll($os, $datastore);
        (new Ospfv3)->discover($os);
        (new Ospfv3)->poll($os, $datastore);

        $this->assertSame(1, $device->bgppeers()->count());
        $this->assertSame(1, $device->bgpPeersCbgp()->count());
        $this->assertSame(1, $device->ospfv3Instances()->count());
        $this->assertSame(1, $device->ospfv3Areas()->count());
    }

    public function testDiscoveryAndPollingLeaveInactiveDataUntilExplicitCleanup(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        Vrf::factory()->for($device)->create([
            'vrf_oid' => '1', 'vrf_name' => 'blue', 'context_name' => 'blue',
            'context_enabled' => true, 'context_verified' => true, 'context_failures' => 0,
        ]);
        BgpPeer::factory()->for($device)->create(['context_name' => 'blue']);
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverVrfs')->andReturnUsing(fn () => collect([
            new Vrf(['vrf_oid' => '1', 'vrf_name' => 'blue', 'context_name' => 'blue']),
        ]));
        $os->shouldReceive('discoverVrfPorts')->andReturn(collect());
        $module = new FailingContextVrf;

        for ($i = 1; $i <= VrfModule::CONTEXT_FAILURE_LIMIT; $i++) {
            $module->discover($os);
            $this->assertSame(1, $device->bgppeers()->count());
        }
        $this->assertFalse($device->vrfs()->sole()->context_verified);
        $module->poll($os, Mockery::mock(DataStorageInterface::class));
        $this->assertSame(1, $device->bgppeers()->count());
        VrfContextCleanup::clean($device, 'blue');
        $this->assertSame(0, $device->bgppeers()->count());
    }

    public function testRoutingTabRemainsAvailableForOrphanedNonBgpData(): void
    {
        $this->withoutMiddleware(CheckInstalled::class);
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $device = Device::factory()->create();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'context_name' => 'removed']);

        $this->actingAs($admin)->get(route('device.edit', $device))
            ->assertOk()->assertSee(route('device.edit.routing', $device));
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function createRow(string $modelClass, Device $device, Port $port, ?string $context): void
    {
        if ($modelClass === BgpPeerCbgp::class) {
            $family = new BgpPeerCbgp;
            $family->forceFill([
                'bgpPeerIdentifier' => '192.0.2.1', 'afi' => 'ipv4', 'safi' => 'unicast',
                'context_name' => $context,
            ]);
            foreach (BgpPeerCbgp::emptyCounters() as $key => $value) {
                $family->setAttribute($key, $value);
            }
            $device->bgpPeersCbgp()->save($family);

            return;
        }

        $attributes = ['context_name' => $context];
        if (in_array($modelClass, [Ipv4Address::class, Ipv6Address::class], true)) {
            $attributes['port_id'] = $port->port_id;
        } else {
            $attributes['device_id'] = $device->device_id;
        }
        if ($modelClass === OspfPort::class) {
            $attributes['port_id'] = $port->port_id;
        }

        Factory::factoryForModel($modelClass)->create($attributes);
    }
}

class FailingContextVrf extends VrfModule
{
    protected function checkContext(string $context): bool
    {
        return false;
    }
}
