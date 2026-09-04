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
        $vrf = \App\Models\Vrf::factory()->for($device)->create([
            'vrf_name' => 'VRF_MPLS_TEST',
            'vrf_oid' => '100',
        ]);
        $remoteDevice = Device::factory()->create(['hostname' => 'remote-r2.local']);
        $remotePort = \App\Models\Port::factory()->for($remoteDevice)->create();
        \App\Models\Ipv4Address::factory()->create([
            'port_id' => $remotePort->port_id,
            'ipv4_address' => '10.0.0.2',
        ]);

        $lsp = \App\Models\MplsLsp::factory()->for($device)->create([
            'mplsLspName' => 'LSP-To-Router-2',
            'mplsLspToAddr' => '10.0.0.2',
            'vrf_oid' => '100',
            'mplsLspAdminState' => 'inService',
            'mplsLspOperState' => 'inService',
            'mplsLspType' => 'dynamic',
        ]);
        \App\Models\MplsLspPath::factory()->for($device)->create([
            'lsp_id' => $lsp->lsp_id,
            'path_oid' => 1,
            'mplsLspPathFailNodeAddr' => '10.0.0.2',
            'mplsLspPathAdminState' => 'inService',
            'mplsLspPathOperState' => 'inService',
        ]);
        $sdp = \App\Models\MplsSdp::factory()->for($device)->create([
            'sdp_oid' => 10,
            'sdpFarEndInetAddress' => '10.0.0.2',
            'sdpAdminStatus' => 'up',
            'sdpOperStatus' => 'up',
            'sdpDescription' => 'SDP-to-R2',
        ]);
        $service = \App\Models\MplsService::factory()->for($device)->create([
            'svc_oid' => 200,
            'svcType' => 'tls',
            'svcVRouterId' => '100',
            'svcAdminStatus' => 'up',
            'svcOperStatus' => 'up',
            'svcDescription' => 'VPLS Service 200',
        ]);
        \App\Models\MplsSdpBind::factory()->for($device)->create([
            'sdp_id' => $sdp->sdp_id,
            'svc_id' => $service->svc_id,
            'sdp_oid' => 10,
            'svc_oid' => 200,
            'sdpBindAdminStatus' => 'up',
            'sdpBindOperStatus' => 'up',
        ]);
        $localPort = \App\Models\Port::factory()->for($device)->create([
            'ifDescr' => 'ge-0/0/1',
            'ifName' => 'ge-0/0/1',
        ]);
        \App\Models\MplsSap::factory()->for($device)->create([
            'svc_id' => $service->svc_id,
            'svc_oid' => 200,
            'ifName' => 'ge-0/0/1',
            'sapPortId' => $localPort->port_id,
            'sapEncapValue' => 100,
            'sapAdminStatus' => 'up',
            'sapOperStatus' => 'up',
        ]);

        // Test LSP view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'lsp']))
            ->assertOk()
            ->assertSee('LSP-To-Router-2')
            ->assertSee('remote-r2.local')
            ->assertSee('VRF_MPLS_TEST');

        // Test Paths view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'paths']))
            ->assertOk()
            ->assertSee('LSP-To-Router-2')
            ->assertSee('remote-r2.local');

        // Test SDPs view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'sdps']))
            ->assertOk()
            ->assertSee('SDP-to-R2')
            ->assertSee('remote-r2.local');

        // Test SDP Binds view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'sdpbinds']))
            ->assertOk()
            ->assertSee('10:200');

        // Test Services view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'services']))
            ->assertOk()
            ->assertSee('VPLS Service 200')
            ->assertSee('VRF_MPLS_TEST');

        // Test SAPs view
        $this->actingAs($this->admin())
            ->get(route('device.routing.mpls', ['device' => $device, 'view' => 'saps']))
            ->assertOk()
            ->assertSee('ge-0/0/1');
    }

    public function testAuthorizedUserCanRenderRoutingBgpWithLinkedPortAndCbgp(): void
    {
        $device = Device::factory()->create(['bgpLocalAs' => 65000]);
        $peerDevice = Device::factory()->create(['hostname' => 'peer-device.local']);
        $peerPort = \App\Models\Port::factory()->for($peerDevice)->create();
        \App\Models\Ipv4Address::factory()->create([
            'port_id' => $peerPort->port_id,
            'ipv4_address' => '192.0.2.10',
        ]);

        \App\Models\BgpPeer::factory()->for($device)->create([
            'bgpPeerIdentifier' => '192.0.2.10',
            'bgpPeerRemoteAs' => 65010,
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpPeerDescr' => 'Peer-With-Linked-Port',
        ]);

        \App\Models\BgpPeerCbgp::unguard();
        \App\Models\BgpPeerCbgp::create([
            'device_id' => $device->device_id,
            'bgpPeerIdentifier' => '192.0.2.10',
            'afi' => 'ipv4',
            'safi' => 'unicast',
            'AcceptedPrefixes' => 10,
            'DeniedPrefixes' => 0,
            'PrefixAdminLimit' => 100,
            'PrefixThreshold' => 80,
            'PrefixClearThreshold' => 70,
            'AdvertisedPrefixes' => 5,
            'SuppressedPrefixes' => 0,
            'WithdrawnPrefixes' => 0,
            'AcceptedPrefixes_delta' => 0,
            'AcceptedPrefixes_prev' => 10,
            'DeniedPrefixes_delta' => 0,
            'DeniedPrefixes_prev' => 0,
            'AdvertisedPrefixes_delta' => 0,
            'AdvertisedPrefixes_prev' => 5,
            'SuppressedPrefixes_delta' => 0,
            'SuppressedPrefixes_prev' => 0,
            'WithdrawnPrefixes_delta' => 0,
            'WithdrawnPrefixes_prev' => 0,
        ]);
        \App\Models\BgpPeerCbgp::reguard();

        $this->actingAs($this->admin())
            ->get(route('device.routing.bgp', ['device' => $device]))
            ->assertOk()
            ->assertSee('192.0.2.10')
            ->assertSee('peer-device.local')
            ->assertSee('ipv4.unicast');
    }

    public function testAuthorizedUserCanRenderRoutingOspfAreaPortCountsAndNeighbors(): void
    {
        $device = Device::factory()->create();
        $remoteDevice = Device::factory()->create(['hostname' => 'ospf-neighbor.local']);
        $remotePort = \App\Models\Port::factory()->for($remoteDevice)->create();
        \App\Models\Ipv4Address::factory()->create([
            'port_id' => $remotePort->port_id,
            'ipv4_address' => '10.255.255.99',
        ]);

        $instance = \App\Models\OspfInstance::factory()->for($device)->create([
            'ospfRouterId' => '10.255.255.10',
            'ospfAdminStat' => 'enabled',
        ]);
        $area = \App\Models\OspfArea::factory()->for($device)->create([
            'ospfAreaId' => '0.0.0.1',
        ]);
        $port1 = \App\Models\Port::factory()->for($device)->create(['ifDescr' => 'eth1']);
        $port2 = \App\Models\Port::factory()->for($device)->create(['ifDescr' => 'eth2']);
        \App\Models\OspfPort::factory()->for($device)->create([
            'port_id' => $port1->port_id,
            'ospf_port_id' => '1',
            'ospfIfAreaId' => '0.0.0.1',
            'ospfIfAdminStat' => 'enabled',
        ]);
        \App\Models\OspfPort::factory()->for($device)->create([
            'port_id' => $port2->port_id,
            'ospf_port_id' => '2',
            'ospfIfAreaId' => '0.0.0.1',
            'ospfIfAdminStat' => 'disabled',
        ]);
        \App\Models\OspfNbr::factory()->for($device)->create([
            'ospfNbrRtrId' => '10.255.255.99',
            'ospfNbrIpAddr' => '192.168.1.99',
            'ospfNbrState' => 'full',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.routing.ospf', ['device' => $device]))
            ->assertOk()
            ->assertSee('10.255.255.10')
            ->assertSee('0.0.0.1')
            ->assertSee('ospf-neighbor.local')
            ->assertSee('2 (1)'); // 2 ports total, 1 enabled
    }

    public function testAuthorizedUserCanQueryRoutesTableAjax(): void
    {
        $device = Device::factory()->create();
        $port = \App\Models\Port::factory()->for($device)->create(['ifDescr' => 'eth0']);
        \App\Models\Route::factory()->for($device)->create([
            'port_id' => $port->port_id,
            'context_name' => 'default',
            'inetCidrRouteIfIndex' => 1,
            'inetCidrRouteDest' => '192.168.50.0',
            'inetCidrRoutePfxLen' => 24,
            'inetCidrRouteNextHop' => '192.168.50.1',
            'inetCidrRouteDestType' => 'ipv4',
            'inetCidrRouteNextHopType' => 'ipv4',
            'inetCidrRouteType' => 3,
            'inetCidrRouteProto' => 3,
            'inetCidrRouteMetric1' => 1,
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(url('ajax/table/routes'), [
                'device_id' => $device->device_id,
                'showAllRoutes' => 'true',
                'showProtocols' => 'all',
                'current' => 1,
                'rowCount' => 10,
            ]);

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['inetCidrRouteDest' => '192.168.50.0']);
    }

    public function testRoutingIndexRedirectsToDefaultProtocol(): void
    {
        $device = Device::factory()->create();
        \App\Models\BgpPeer::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device.routing.index', ['device' => $device]))
            ->assertRedirect(route('device.routing.bgp', ['device' => $device]));
    }

    public function testRoutingIndexRedirectsWithLegacyProtoPath(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get('/device/' . $device->device_id . '/routing/proto=ospf/')
            ->assertRedirect(route('device.routing.ospf', ['device' => $device]));
    }

    public function testRoutingIndexRedirectsWithLegacyPathAndQueryVars(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get('/device/' . $device->device_id . '/routing/proto=mpls/view=paths/')
            ->assertRedirect(route('device.routing.mpls', ['device' => $device, 'view' => 'paths']));
    }

    public function testRoutingIndexRedirectsWithUnderscoredProto(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get('/device/' . $device->device_id . '/routing/proto=ipsec_tunnels/')
            ->assertRedirect(route('device.routing.ipsec-tunnels', ['device' => $device]));
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
