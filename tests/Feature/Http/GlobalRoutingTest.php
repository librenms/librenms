<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\BgpPeer;
use App\Models\CefSwitching;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Ipv4Address;
use App\Models\IsisAdjacency;
use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\Models\OspfInstance;
use App\Models\OspfPort;
use App\Models\Ospfv3Instance;
use App\Models\Port;
use App\Models\User;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class GlobalRoutingTest extends TestCase
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

    public function testIndexRedirectsToFirstAvailableProtocol(): void
    {
        Vrf::factory()->for(Device::factory())->create();

        $this->actingAs($this->admin())
            ->get('routing')
            ->assertRedirect(route('routing.vrf'));
    }

    public function testLegacyUrlsRedirect(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('routing/protocol=bgp/type=external/graph=NULL')
            ->assertRedirect(route('routing.bgp', ['type' => 'external']));

        $this->actingAs($admin)
            ->get('routing/protocol=bgp/adminstatus=start/state=down')
            ->assertRedirect(route('routing.bgp', ['adminstatus' => 'start', 'state' => 'down']));

        $this->actingAs($admin)
            ->get('routing/protocol=bgp/view=graphs/graph=updates')
            ->assertRedirect(route('routing.bgp', ['graph' => 'updates']));

        $this->actingAs($admin)
            ->get('routing/protocol=mpls/view=sdps')
            ->assertRedirect(route('routing.mpls', ['view' => 'sdps']));

        $this->actingAs($admin)
            ->get('routing/protocol=vrf/vrf=MGMT')
            ->assertRedirect(route('routing.vrf', ['vrf' => 'MGMT']));

        $this->actingAs($admin)
            ->get('routing/protocol=bogus')
            ->assertNotFound();
    }

    public function testBgpPageFilters(): void
    {
        $device = Device::factory()->create(['bgpLocalAs' => 65000, 'hostname' => 'bgp-router.local']);
        BgpPeer::factory()->for($device)->create([
            'bgpPeerIdentifier' => '192.0.2.1',
            'bgpLocalAddr' => '192.0.2.254',
            'bgpPeerRemoteAs' => 65000,
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpPeerDescr' => 'Internal-Peer',
        ]);
        BgpPeer::factory()->for($device)->create([
            'bgpPeerIdentifier' => '198.51.100.1',
            'bgpPeerRemoteAs' => 64999,
            'bgpPeerState' => 'idle',
            'bgpPeerAdminStatus' => 'start',
            'bgpPeerDescr' => 'External-Peer',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('routing.bgp'))
            ->assertOk()
            ->assertSee('bgp-router.local')
            ->assertSee('192.0.2.254')
            ->assertSee('Internal-Peer')
            ->assertSee('External-Peer')
            ->assertSee('Priv eBGP');

        $this->actingAs($admin)
            ->get(route('routing.bgp', ['type' => 'internal']))
            ->assertOk()
            ->assertSee('Internal-Peer')
            ->assertDontSee('External-Peer');

        $this->actingAs($admin)
            ->get(route('routing.bgp', ['type' => 'external']))
            ->assertOk()
            ->assertDontSee('Internal-Peer')
            ->assertSee('External-Peer');

        $this->actingAs($admin)
            ->get(route('routing.bgp', ['adminstatus' => 'start', 'state' => 'down']))
            ->assertOk()
            ->assertDontSee('Internal-Peer')
            ->assertSee('External-Peer');

        $this->actingAs($admin)
            ->get(route('routing.bgp', ['graph' => 'updates']))
            ->assertOk()
            ->assertSee('bgp_updates');

        $this->actingAs($admin)
            ->get(route('routing.bgp', ['graph' => 'bogus']))
            ->assertRedirect();
    }

    public function testBgpPageOnlyShowsPermittedDevices(): void
    {
        $allowed = Device::factory()->create();
        $denied = Device::factory()->create();
        BgpPeer::factory()->for($allowed)->create(['bgpPeerDescr' => 'Allowed-Peer']);
        BgpPeer::factory()->for($denied)->create(['bgpPeerDescr' => 'Denied-Peer']);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($allowed->device_id);

        $this->actingAs($user)
            ->get(route('routing.bgp'))
            ->assertOk()
            ->assertSee('Allowed-Peer')
            ->assertDontSee('Denied-Peer');
    }

    public function testCefPage(): void
    {
        $device = Device::factory()->create(['hostname' => 'cef-router.local']);
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 10,
            'entPhysicalName' => 'RP0',
            'entPhysicalModelName' => 'ASR1000-RP2',
        ]);
        CefSwitching::factory()->for($device)->create([
            'entPhysicalIndex' => 10,
            'afi' => 'ipv4',
            'cef_path' => 'RP RIB',
        ]);

        $this->actingAs($this->admin())
            ->get(route('routing.cef'))
            ->assertOk()
            ->assertSee('cef-router.local')
            ->assertSee('RP0 (ASR1000-RP2)')
            ->assertSee('RP RIB');

        $this->actingAs($this->admin())
            ->get(route('routing.cef', ['view' => 'graphs']))
            ->assertOk()
            ->assertSee('cefswitching_graph');
    }

    public function testIsisPageStateFilter(): void
    {
        $device = Device::factory()->create(['hostname' => 'isis-router.local']);
        IsisAdjacency::factory()->for($device)->create([
            'isisISAdjState' => 'up',
            'isisISAdjNeighSysID' => '0000.0000.0001',
        ]);
        IsisAdjacency::factory()->for($device)->create([
            'isisISAdjState' => 'down',
            'isisISAdjNeighSysID' => '0000.0000.0002',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('routing.isis'))
            ->assertOk()
            ->assertSee('isis-router.local')
            ->assertSee('0000.0000.0001')
            ->assertSee('0000.0000.0002');

        $this->actingAs($admin)
            ->get(route('routing.isis', ['state' => 'down']))
            ->assertOk()
            ->assertDontSee('0000.0000.0001')
            ->assertSee('0000.0000.0002');
    }

    public function testMplsPageViews(): void
    {
        $device = Device::factory()->create(['hostname' => 'mpls-router.local']);
        Vrf::factory()->for($device)->create(['vrf_name' => 'VRF_MPLS_TEST', 'vrf_oid' => '100']);
        $remote = Device::factory()->create(['hostname' => 'remote-r2.local']);
        $remotePort = Port::factory()->for($remote)->create();
        Ipv4Address::factory()->create(['port_id' => $remotePort->port_id, 'ipv4_address' => '10.0.0.2']);

        $lsp = MplsLsp::factory()->for($device)->create([
            'mplsLspName' => 'LSP-To-Router-2',
            'mplsLspToAddr' => '10.0.0.2',
            'vrf_oid' => '100',
            'mplsLspLastChange' => 90061,
        ]);
        MplsLspPath::factory()->for($device)->create(['lsp_id' => $lsp->lsp_id, 'path_oid' => 7]);
        $sdp = MplsSdp::factory()->for($device)->create(['sdp_oid' => 10, 'sdpDescription' => 'SDP-to-R2', 'sdpFarEndInetAddress' => '10.0.0.2']);
        $service = MplsService::factory()->for($device)->create([
            'svc_oid' => 200,
            'svcVRouterId' => '100',
            'svcDescription' => 'VPLS Service 200',
        ]);
        MplsSdpBind::factory()->for($device)->create([
            'sdp_id' => $sdp->sdp_id,
            'svc_id' => $service->svc_id,
            'sdp_oid' => 10,
            'svc_oid' => 200,
        ]);
        $sapPort = Port::factory()->for($device)->create(['ifName' => '1/1/1']);
        MplsSap::factory()->for($device)->create([
            'svc_id' => $service->svc_id,
            'svc_oid' => 200,
            'ifName' => '1/1/1',
            'sapPortId' => 1234,
            'sapEncapValue' => '*',
            'sapDescription' => 'Customer-SAP',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('routing.mpls'))
            ->assertOk()
            ->assertSee('mpls-router.local')
            ->assertSee('LSP-To-Router-2')
            ->assertSee('remote-r2.local')
            ->assertSee('VRF_MPLS_TEST')
            ->assertSee('1 day 1 hour 1 minute 1 second');

        $this->actingAs($admin)
            ->get(route('routing.mpls', ['view' => 'paths']))
            ->assertOk()
            ->assertSee('LSP-To-Router-2');

        $this->actingAs($admin)
            ->get(route('routing.mpls', ['view' => 'sdps']))
            ->assertOk()
            ->assertSee('SDP-to-R2')
            ->assertSee('remote-r2.local');

        $this->actingAs($admin)
            ->get(route('routing.mpls', ['view' => 'sdpbinds']))
            ->assertOk()
            ->assertSee('10:200');

        $this->actingAs($admin)
            ->get(route('routing.mpls', ['view' => 'services']))
            ->assertOk()
            ->assertSee('VPLS Service 200')
            ->assertSee('VRF_MPLS_TEST');

        $this->actingAs($admin)
            ->get(route('routing.mpls', ['view' => 'saps']))
            ->assertOk()
            ->assertSee('Customer-SAP')
            ->assertSee('port=' . $sapPort->port_id, false)
            ->assertSee('200.1234.4095');
    }

    public function testOspfPages(): void
    {
        $device = Device::factory()->create(['hostname' => 'ospf-router.local']);
        OspfInstance::factory()->for($device)->create([
            'ospfRouterId' => '10.255.255.1',
            'ospfAdminStat' => 'enabled',
        ]);
        $port = Port::factory()->for($device)->create();
        OspfPort::factory()->for($device)->count(2)->sequence(
            ['ospfIfAdminStat' => 'enabled', 'ospf_port_id' => '1'],
            ['ospfIfAdminStat' => 'disabled', 'ospf_port_id' => '2'],
        )->create(['port_id' => $port->port_id]);
        Ospfv3Instance::factory()->for($device)->create([
            'router_id' => '10.255.255.6',
        ]);

        $this->actingAs($this->admin())
            ->get(route('routing.ospf'))
            ->assertOk()
            ->assertSee('ospf-router.local')
            ->assertSee('10.255.255.1')
            ->assertSee('2 (1)');

        $this->actingAs($this->admin())
            ->get(route('routing.ospfv3'))
            ->assertOk()
            ->assertSee('ospf-router.local')
            ->assertSee('10.255.255.6');
    }

    public function testVrfPage(): void
    {
        $device = Device::factory()->create(['hostname' => 'vrf-router.local']);
        $vrf = Vrf::factory()->for($device)->create([
            'vrf_name' => 'MGMT_VRF',
            'mplsVpnVrfDescription' => 'Management Network',
            'mplsVpnVrfRouteDistinguisher' => '65000:100',
        ]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'OTHER_VRF']);
        Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifVrf' => $vrf->vrf_id]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('routing.vrf'))
            ->assertOk()
            ->assertSee('MGMT_VRF')
            ->assertSee('OTHER_VRF')
            ->assertSee('Management Network')
            ->assertSee('65000:100')
            ->assertSee('vrf-router.local')
            ->assertSee('Gi0/1');

        $this->actingAs($admin)
            ->get(route('routing.vrf', ['vrf' => 'MGMT_VRF', 'view' => 'graphs', 'graph' => 'bits']))
            ->assertOk()
            ->assertDontSee('OTHER_VRF')
            ->assertSee('type=port_bits', false);
    }

    public function testCiscoOtvPage(): void
    {
        $device = Device::factory()->create(['hostname' => 'otv-router.local']);
        $component = new \LibreNMS\Component();
        $overlayId = key($component->createComponent($device->device_id, 'Cisco-OTV'));
        $adjacencyId = key($component->createComponent($device->device_id, 'Cisco-OTV'));
        $component->setComponentPrefs($device->device_id, [
            $overlayId => [
                'otvtype' => 'overlay',
                'index' => '1',
                'label' => 'Overlay1',
                'transport' => 'Multicast',
                'status' => 0,
                'ignore' => 0,
                'disabled' => 0,
            ],
            $adjacencyId => [
                'otvtype' => 'adjacency',
                'index' => '1',
                'label' => 'Overlay1 - remote-otv',
                'endpoint' => '192.0.2.50',
                'status' => 1,
                'error' => 'Adjacency is Down',
                'ignore' => 0,
                'disabled' => 0,
            ],
        ]);

        $this->actingAs($this->admin())
            ->get(route('routing.cisco-otv'))
            ->assertOk()
            ->assertSee('otv-router.local')
            ->assertSee('Overlay1 - Multicast')
            ->assertSee('192.0.2.50')
            ->assertSee('Adjacency is Down');
    }

    public function testUserWithoutRoutingPermissionIsForbidden(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)
            ->get(route('routing.bgp'))
            ->assertForbidden();
    }
}
