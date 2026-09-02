<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\CefSwitching;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class RoutingCefTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testAuthorizedUserCanRenderRoutingCefTab(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 10,
            'entPhysicalName' => 'RP0',
            'entPhysicalModelName' => 'ASR1000-RP2',
        ]);
        CefSwitching::factory()->for($device)->create([
            'entPhysicalIndex' => 10,
            'afi' => 'ipv4',
            'cef_index' => 1,
            'cef_path' => 'RP RIB',
            'drop' => 100,
            'drop_prev' => 50,
            'punt' => 200,
            'punt_prev' => 100,
            'punt2host' => 300,
            'punt2host_prev' => 150,
            'updated' => 1000,
            'updated_prev' => 950,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.cef', ['device' => $device]))
            ->assertOk()
            ->assertSee('RP0 (ASR1000-RP2)')
            ->assertSee('RP RIB')
            ->assertSee('Process switching with CEF assistance.')
            ->assertSee('(1/sec)');
    }

    public function testRoutingCefTabGraphsView(): void
    {
        $device = Device::factory()->create();
        CefSwitching::factory()->for($device)->create([
            'entPhysicalIndex' => 10,
            'afi' => 'ipv4',
            'cef_index' => 1,
            'cef_path' => 'RP LES',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.cef', ['device' => $device, 'view' => 'graphs']))
            ->assertOk()
            ->assertSee('cefswitching_graph');
    }

    public function testAuthorizedUserCanRenderRoutingIpsecTunnelsTab(): void
    {
        $device = Device::factory()->create();
        \App\Models\IpsecTunnel::factory()->create([
            'device_id' => $device->device_id,
            'local_addr' => '192.168.1.1',
            'peer_addr' => '10.0.0.1',
            'tunnel_name' => 'SiteA-to-SiteB',
            'tunnel_status' => 'active',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.ipsec-tunnels', ['device' => $device]))
            ->assertOk()
            ->assertSee('SiteA-to-SiteB')
            ->assertSee('192.168.1.1')
            ->assertSee('10.0.0.1')
            ->assertSee('active');

        $this->actingAs($this->admin())
            ->get(route('device.routing.ipsec-tunnels', ['device' => $device, 'view' => 'graphs', 'graph' => 'bits']))
            ->assertOk()
            ->assertSee('ipsectunnel_bits');
    }

    public function testAuthorizedUserCanRenderRoutingVrfTab(): void
    {
        $device = Device::factory()->create();
        $port = \App\Models\Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1']);
        $vrf = \App\Models\Vrf::factory()->for($device)->create([
            'vrf_name' => 'MGMT_VRF',
            'mplsVpnVrfDescription' => 'Management Network',
            'mplsVpnVrfRouteDistinguisher' => '65000:100',
        ]);
        $port->update(['ifVrf' => $vrf->vrf_id]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.vrf', ['device' => $device]))
            ->assertOk()
            ->assertSee('MGMT_VRF')
            ->assertSee('Management Network')
            ->assertSee('65000:100')
            ->assertSee('Gi0/1');

        $this->actingAs($this->admin())
            ->get(route('device.routing.vrf', ['device' => $device, 'view' => 'graphs', 'graph' => 'bits']))
            ->assertOk()
            ->assertSee('type=port_bits', false);
    }

    public function testAuthorizedUserCanRenderRoutingCiscoOtvTab(): void
    {
        $device = Device::factory()->create();
        $component = new \LibreNMS\Component();
        $created = $component->createComponent($device->device_id, 'Cisco-OTV');
        $componentId = key($created);
        $component->setComponentPrefs($device->device_id, [
            $componentId => [
                'otvtype' => 'overlay',
                'index' => '1',
                'label' => 'Overlay1',
                'transport' => 'Multicast',
                'status' => 0,
                'ignore' => 0,
                'disabled' => 0,
            ],
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.cisco-otv', ['device' => $device]))
            ->assertOk()
            ->assertSee('Overlay1')
            ->assertSee('Multicast')
            ->assertSee('device_cisco-otv-vlan')
            ->assertSee('device_cisco-otv-mac');
    }

    public function testAuthorizedUserCanRenderRoutingOspfTab(): void
    {
        $device = Device::factory()->create();
        \App\Models\OspfInstance::factory()->for($device)->create([
            'ospfRouterId' => '10.255.255.1',
            'ospfAdminStat' => 'enabled',
            'ospfAreaBdrRtrStatus' => 'false',
            'ospfASBdrRtrStatus' => 'true',
        ]);
        \App\Models\OspfArea::factory()->for($device)->create([
            'ospfAreaId' => '0.0.0.0',
        ]);
        \App\Models\OspfNbr::factory()->for($device)->create([
            'ospfNbrRtrId' => '10.255.255.2',
            'ospfNbrIpAddr' => '192.168.10.2',
            'ospfNbrState' => 'full',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.ospf', ['device' => $device]))
            ->assertOk()
            ->assertSee('10.255.255.1')
            ->assertSee('0.0.0.0')
            ->assertSee('10.255.255.2')
            ->assertSee('192.168.10.2')
            ->assertSee('full');
    }

    public function testAuthorizedUserCanRenderRoutingOspfv3Tab(): void
    {
        $device = Device::factory()->create();
        $instance = \App\Models\Ospfv3Instance::factory()->for($device)->create([
            'router_id' => '10.0.0.1',
            'ospfv3RouterId' => 167772161,
            'ospfv3AdminStatus' => 'enabled',
            'ospfv3AreaBdrRtrStatus' => 'true',
            'ospfv3ASBdrRtrStatus' => 'false',
        ]);
        \App\Models\Ospfv3Area::factory()->for($device)->create([
            'ospfv3_instance_id' => $instance->id,
            'ospfv3AreaId' => ip2long('0.0.0.0'),
            'ospfv3AreaScopeLsaCount' => 15,
        ]);
        \App\Models\Ospfv3Nbr::factory()->for($device)->create([
            'ospfv3_instance_id' => $instance->id,
            'router_id' => '10.0.0.2',
            'ospfv3NbrAddress' => 'fe80::1',
            'ospfv3NbrState' => 'full',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.ospfv3', ['device' => $device]))
            ->assertOk()
            ->assertSee('10.0.0.1')
            ->assertSee('0.0.0.0')
            ->assertSee('10.0.0.2')
            ->assertSee('fe80::1')
            ->assertSee('full');
    }

    public function testAuthorizedUserCanRenderRoutingIsisTab(): void
    {
        $device = Device::factory()->create();
        \App\Models\IsisAdjacency::factory()->for($device)->create([
            'isisISAdjIPAddrAddress' => '10.10.10.1',
            'isisISAdjNeighSysID' => '0000.0000.0001',
            'isisISAdjAreaAddress' => '49.0001',
            'isisISAdjState' => 'up',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.isis', ['device' => $device]))
            ->assertOk()
            ->assertSee('10.10.10.1')
            ->assertSee('0000.0000.0001')
            ->assertSee('49.0001')
            ->assertSee('up');
    }

    public function testAuthorizedUserCanRenderRoutingRoutesTab(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.routing.routes', ['device' => $device]))
            ->assertOk()
            ->assertSee('routes')
            ->assertSee('ajax/table/routes')
            ->assertSee('Destination');
    }

    public function testAuthorizedUserCanRenderRoutingBgpTab(): void
    {
        $device = Device::factory()->create(['bgpLocalAs' => 65000]);
        \App\Models\BgpPeer::factory()->for($device)->create([
            'bgpPeerIdentifier' => '192.0.2.1',
            'bgpPeerRemoteAs' => 65001,
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpPeerDescr' => 'Core-Peer-1',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.bgp', ['device' => $device]))
            ->assertOk()
            ->assertSee('65000')
            ->assertSee('192.0.2.1')
            ->assertSee('65001')
            ->assertSee('Core-Peer-1')
            ->assertSee('established');
    }

    public function testAuthorizedUserCanRenderRoutingMplsTab(): void
    {
        $device = Device::factory()->create();
        \App\Models\MplsLsp::factory()->for($device)->create([
            'mplsLspName' => 'LSP-To-Router-2',
            'mplsLspToAddr' => '10.0.0.2',
            'mplsLspAdminState' => 'inService',
            'mplsLspOperState' => 'inService',
            'mplsLspType' => 'dynamic',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device]))
            ->assertOk()
            ->assertSee('LSP-To-Router-2')
            ->assertSee('10.0.0.2')
            ->assertSee('inService')
            ->assertSee('dynamic');
    }

    public function testRoutingIndexRedirectsToDefaultProtocol(): void
    {
        $device = Device::factory()->create();
        \App\Models\BgpPeer::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device.routing.index', ['device' => $device]))
            ->assertRedirect(route('device.routing.bgp', ['device' => $device]));
    }

    public function testRoutingIndexRedirectsWithLegacyProtoParameter(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.routing.index', ['device' => $device, 'proto' => 'ospf']))
            ->assertRedirect(route('device.routing.ospf', ['device' => $device]));
    }

    public function testUserWithoutRoutingPermissionGetsForbidden(): void
    {
        $device = Device::factory()->create();
        CefSwitching::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device.routing.cef', ['device' => $device]))
            ->assertForbidden();
    }
}
