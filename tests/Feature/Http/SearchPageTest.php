<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\Link;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use App\Models\Vlan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class SearchPageTest extends TestCase
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

    /**
     * @param  array<string, scalar>  $params
     * @return \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse>
     */
    private function results(string $source, string $query, array $params = [], ?User $user = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user ?? $this->admin())
            ->getJson(route("search.results.$source", ['q' => $query, ...$params]))
            ->assertOk();
    }

    public function testPageRendersSources(): void
    {
        $this->actingAs($this->admin())
            ->get(route('search', ['q' => '10.0.0.1', 'sources' => 'arp,fdb,bogus']))
            ->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->pluck('url', 'key')->get('ndp') == route('search.results.ndp'))
            ->assertViewHas('selected', ['arp', 'fdb'])
            ->assertViewHas('locateUrl', route('search.locate'));
    }

    public function testLegacyUrlsRedirect(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('search/search=arp/searchPhrase=10.0.0.1')
            ->assertRedirect(route('search', ['q' => '10.0.0.1', 'sources' => 'arp']));
        $this->actingAs($admin)->post('search/search=ipv4/', ['address' => '10.1.', 'device_id' => 3])
            ->assertRedirect(route('search', ['q' => '10.1.', 'sources' => 'ipv4', 'device_id' => 3]));
        $this->actingAs($admin)->get('search/search=fdb')
            ->assertRedirect(route('search', ['q' => '', 'sources' => 'fdb']));
        $this->actingAs($admin)->get('search/search=packages')
            ->assertRedirect(route('packages.index'));
    }

    public function testEmptySearchBrowsesEveryEntry(): void
    {
        $first = Device::factory()->create();
        $second = Device::factory()->create();
        foreach ([$first, $second] as $index => $device) {
            $port = Port::factory()->for($device)->create();
            Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => "02b0b0b0b00$index", 'ipv4_address' => "192.0.2.20$index"]);
        }
        $total = Ipv4Mac::count();

        $this->results('arp', '')->assertJson(['applicable' => true, 'total' => $total]);
        $this->results('arp', '', ['device_id' => $first->device_id])->assertJson(['total' => 1]);
        $this->actingAs($this->admin())->getJson(route('search.results.arp'))->assertOk()->assertJson(['total' => $total]);
        // sources that only answer some searches still list everything
        $this->results('ipv6', '')->assertJson(['applicable' => true]);
        $this->actingAs($this->admin())->getJson(route('search.locate', ['q' => '']))->assertUnprocessable();
    }

    public function testPageBrowsesWhenQueryIsPresent(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('search', ['q' => '']))->assertOk()->assertViewHas('browse', true);
        $this->actingAs($admin)->get(route('search'))->assertOk()->assertViewHas('browse', false);
    }

    public function testSourceNotApplicableToTerm(): void
    {
        $this->results('ipv4', '00:11:22:33:44:55')
            ->assertJson(['applicable' => false, 'total' => 0]);
        $this->results('ipv6', '10.0.0.1')
            ->assertJson(['applicable' => false]);
        $this->results('arp', 'fe80::1')
            ->assertJson(['applicable' => false]);
    }

    public function testDevicesByNameAndInterfaceAddress(): void
    {
        $device = Device::factory()->create(['hostname' => 'search-core-sw1.example.com', 'display' => 'search-core-sw1']);
        $port = Port::factory()->for($device)->create(['ifPhysAddress' => '0a1b2c3d4e5f']);
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.7', 'ipv4_prefixlen' => 24]);
        Device::factory()->create(['hostname' => 'unrelated.example.com']);

        $this->results('devices', 'search-core')
            ->assertJson(['applicable' => true, 'total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'search-core-sw1'));

        $this->results('devices', '198.51.100.0/24')->assertJson(['total' => 1]);
        $this->results('devices', '0a:1b:2c:3d:4e:5f')->assertJson(['total' => 1]);
    }

    public function testDevicesRespectAccess(): void
    {
        $visible = Device::factory()->create(['hostname' => 'access-visible.example.com']);
        Device::factory()->create(['hostname' => 'access-hidden.example.com']);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($visible->device_id);

        $this->results('devices', 'access-', user: $user)
            ->assertJson(['total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'access-visible') && ! str_contains($html, 'access-hidden'));
    }

    public function testIpv4AddressesExactNetworkAndPartial(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '203.0.113.9', 'ipv4_prefixlen' => 28]);
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '203.0.113.99', 'ipv4_prefixlen' => 28]);

        $this->results('ipv4', '203.0.113.9')
            ->assertJson(['total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, '203.0.113.9/28'));
        $this->results('ipv4', '203.0.113.0/26')->assertJson(['total' => 1]);
        $this->results('ipv4', '203.0.113.0/24')->assertJson(['total' => 2]);
        $this->results('ipv4', '203.0.113.')->assertJson(['total' => 2]);
    }

    public function testIpv6AddressesMatchCompressedInput(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Ipv6Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv6_address' => '2001:0db8:0000:0000:0000:0000:0000:0042',
            'ipv6_compressed' => '2001:db8::42',
            'ipv6_prefixlen' => 64,
        ]);

        $this->results('ipv6', '2001:db8::42')->assertJson(['total' => 1]);
        $this->results('ipv6', '2001:db8::/32')->assertJson(['total' => 1]);
        $this->results('ipv6', '2001:db8::4')->assertJson(['total' => 1]); // valid address, but typed as a prefix
        $this->results('ipv6', '2001:db9::/32')->assertJson(['total' => 0]);
    }

    public function testInterfaceMacsAnyFormat(): void
    {
        Port::factory()->for(Device::factory())->create(['ifPhysAddress' => 'aabbcc001122']);

        $this->results('mac', 'aabb.cc00.1122')->assertJson(['total' => 1]);
        $this->results('mac', 'AA-BB-CC')->assertJson(['total' => 1]);
    }

    public function testVendorSearch(): void
    {
        LibrenmsConfig::set('mac_oui.enabled', true);
        DB::table('vendor_ouis')->insert(['oui' => 'f0f0f0', 'vendor' => 'Searchtest Widgets Inc']);
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => 'f0f0f0123456', 'ipv4_address' => '192.0.2.44']);

        $this->results('arp', 'searchtest widgets')
            ->assertJson(['applicable' => true, 'total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Searchtest Widgets Inc'));
    }

    public function testArpShowsOwnerAndSwitchPort(): void
    {
        $router = Device::factory()->create();
        $routerPort = Port::factory()->for($router)->create();
        $switch = Device::factory()->create();
        $accessPort = Port::factory()->for($switch)->create(['ifName' => 'access-port-7', 'ifDescr' => 'access-port-7', 'ifAlias' => 'access-port-7']);
        $server = Device::factory()->create(['hostname' => 'owner-server.example.com']);
        Port::factory()->for($server)->create(['ifPhysAddress' => '02aabbccddee']);

        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02aabbccddee', 'ipv4_address' => '192.0.2.77']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $accessPort->port_id, 'mac_address' => '02aabbccddee', 'vlan_id' => 0]);

        $html = $this->results('arp', '192.0.2.77')->assertJson(['total' => 1])->json('html');

        $this->assertStringContainsString('02:aa:bb:cc:dd:ee', $html);
        $this->assertStringContainsString('owner-server', $html);
        $this->assertStringContainsString('access-port-7', $html);
        $this->assertStringContainsString(__('search.badge.only_mac'), $html);
    }

    public function testNdpMatchesCompressedAndPartialAddresses(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Nd::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '021122334455', 'ipv6_address' => 'fe80:0000:0000:0000:0011:22ff:fe33:4455']);

        $this->results('ndp', 'fe80::11:22ff:fe33:4455')->assertJson(['total' => 1]);
        $this->results('ndp', '22ff:fe33')->assertJson(['total' => 1]);
        $this->results('ndp', '02:11:22:33:44:55')->assertJson(['total' => 1]);
    }

    public function testFdbByIpAndUplinkBadge(): void
    {
        $switch = Device::factory()->create();
        $uplink = Port::factory()->for($switch)->create();
        $vlan = Vlan::factory()->create(['device_id' => $switch->device_id, 'vlan_vlan' => 342]);
        Link::factory()->create(['local_port_id' => $uplink->port_id, 'local_device_id' => $switch->device_id]);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $uplink->port_id, 'mac_address' => '02cafe000001', 'vlan_id' => $vlan->vlan_id]);
        Ipv4Mac::factory()->create(['device_id' => $switch->device_id, 'port_id' => $uplink->port_id, 'mac_address' => '02cafe000001', 'ipv4_address' => '192.0.2.88']);

        $html = $this->results('fdb', '192.0.2.88')->assertJson(['total' => 1])->json('html');

        $this->assertStringContainsString('02:ca:fe:00:00:01', $html);
        $this->assertStringContainsString('192.0.2.88', $html);
        $this->assertStringContainsString('342', $html);
        $this->assertStringContainsString(__('search.badge.uplink'), $html);
    }

    public function testDeviceFilter(): void
    {
        $first = Device::factory()->create();
        $second = Device::factory()->create();
        foreach ([$first, $second] as $device) {
            $port = Port::factory()->for($device)->create();
            PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02beef000002']);
        }

        $this->results('fdb', '02:be:ef:00:00:02')->assertJson(['total' => 2]);
        $this->results('fdb', '02:be:ef:00:00:02', ['device_id' => $first->device_id])->assertJson(['total' => 1]);
    }

    public function testLocatePrefersAccessPortOverUplink(): void
    {
        $router = Device::factory()->create();
        $routerPort = Port::factory()->for($router)->create();
        $switch = Device::factory()->create();
        $uplink = Port::factory()->for($switch)->create(['ifName' => 'uplink-port', 'ifDescr' => 'uplink-port', 'ifAlias' => 'uplink-port']);
        $access = Port::factory()->for($switch)->create(['ifName' => 'access-port', 'ifDescr' => 'access-port', 'ifAlias' => 'access-port']);
        Link::factory()->create(['local_port_id' => $uplink->port_id, 'local_device_id' => $switch->device_id]);

        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02dead000003', 'ipv4_address' => '192.0.2.99']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $uplink->port_id, 'mac_address' => '02dead000003']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $access->port_id, 'mac_address' => '02dead000003']);
        Ipv4Address::factory()->create(['port_id' => $routerPort->port_id, 'ipv4_address' => '192.0.2.1', 'ipv4_prefixlen' => 24]);

        $admin = $this->admin();
        foreach (['192.0.2.99', '02:de:ad:00:00:03'] as $query) {
            $html = $this->actingAs($admin)->getJson(route('search.locate', ['q' => $query]))->assertOk()->json('html');

            $this->assertStringContainsString(__('search.locate.connected'), $html);
            $this->assertMatchesRegularExpression('/' . preg_quote(__('search.locate.connected'), '/') . '.*access-port.*' . preg_quote(__('search.locate.also_seen'), '/') . '.*uplink-port/s', $html);
            $this->assertStringContainsString('192.0.2.1/24', $html); // gateway in the same subnet
            $this->assertStringContainsString('02:de:ad:00:00:03', $html);
        }
    }

    public function testLocateIgnoresTermsThatAreNotOneHost(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->getJson(route('search.locate', ['q' => '10.0.0.0/8']))->assertOk()->assertJson(['html' => '']);
        $this->actingAs($admin)->getJson(route('search.locate', ['q' => '00:11:22']))->assertOk()->assertJson(['html' => '']);
        $this->actingAs($admin)->getJson(route('search.locate', ['q' => 'cisco']))->assertOk()->assertJson(['html' => '']);
    }

    public function testPortSourcesRequirePortAccess(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->getJson(route('search.results.arp', ['q' => '10.0.0.1']))->assertForbidden();
        $this->actingAs($user)->getJson(route('search.locate', ['q' => '10.0.0.1']))->assertForbidden();
    }

    public function testSeveralPastedAddressesAreSearchedTogether(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        foreach (['192.0.2.31', '192.0.2.32', '192.0.2.33'] as $index => $ip) {
            Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => "02c0ffee000$index", 'ipv4_address' => $ip]);
        }

        $this->results('arp', "192.0.2.31\n02:c0:ff:ee:00:01")->assertJson(['total' => 2]);
        // the IPv6 source only gets the terms it can answer
        $this->results('ndp', '192.0.2.31 02:c0:ff:ee:00:01')->assertJson(['applicable' => true]);
        $this->results('ipv6', '192.0.2.31 192.0.2.32')->assertJson(['applicable' => false]);

        $html = $this->actingAs($this->admin())
            ->getJson(route('search.locate', ['q' => 'ip="192.0.2.31", 192.0.2.33']))
            ->assertOk()->json('html');
        $this->assertSame(2, substr_count($html, __('search.locate.title')));
        $this->assertStringContainsString("formatMac('02c0ffee0002')", $html);
    }

    public function testRemembersSourcesAndMacFormat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('preferences.store'), ['pref' => 'network_search_sources', 'value' => 'arp,fdb'])->assertOk();
        $this->actingAs($admin)->postJson(route('preferences.store'), ['pref' => 'network_search_mac_format', 'value' => 'dot'])->assertOk();
        $this->actingAs($admin)->postJson(route('preferences.store'), ['pref' => 'network_search_sources', 'value' => 'arp;drop'])->assertUnprocessable();

        $this->actingAs($admin)->get(route('search'))
            ->assertViewHas('selected', ['arp', 'fdb'])
            ->assertViewHas('macFormat', 'dot');
        // a shared link keeps its own sources
        $this->actingAs($admin)->get(route('search', ['sources' => 'ndp']))->assertViewHas('selected', ['ndp']);
    }

    public function testGlobalSearchGroupsLinkToAllResults(): void
    {
        $device = Device::factory()->create(['hostname' => 'viewall-test.example.com']);
        $port = Port::factory()->for($device)->create();
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02facade0001']);
        LibrenmsConfig::set('webui.global_search.fdb', true);
        $admin = $this->admin();

        $this->actingAs($admin)->getJson(route('ajax.search.devices', ['search' => 'viewall-test']))
            ->assertJsonPath('groups.0.url', route('search', ['q' => 'viewall-test', 'sources' => 'devices']));
        $this->actingAs($admin)->getJson(route('ajax.search.fdb', ['search' => '02:fa:ca:de:00:01']))
            ->assertJsonPath('groups.0.url', route('search', ['q' => '02:fa:ca:de:00:01', 'sources' => 'fdb']));
        // too short to be a MAC on the search page, so there is nothing to link to
        $this->actingAs($admin)->getJson(route('ajax.search.fdb', ['search' => 'faca']))
            ->assertJsonPath('groups.0.type', 'fdb_tables')
            ->assertJsonPath('groups.0.url', null);
    }
}
