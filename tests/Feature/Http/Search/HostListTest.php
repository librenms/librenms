<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HostListTest extends SearchTestCase
{
    public function testPartialMatchesAreListedPerHost(): void
    {
        [$device, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b0b0b0b001', 'ipv4_address' => '203.0.113.9']);
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b0b0b0b002', 'ipv4_address' => '203.0.113.99']);
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '203.0.113.9', 'ipv4_prefixlen' => 28]);
        // no IP known, listed by MAC
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b0b0b0b003']);
        // already listed by its IP
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b0b0b0b001']);

        $this->results(['q' => '203.0.113.'])->assertJson(['mode' => 'list', 'total' => 2]);
        $this->results(['q' => '203.0.113.0/28'])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->results(['q' => '02:b0:b0'])->assertJson(['mode' => 'list', 'total' => 3]);
    }

    public function testEmptySearchListsEverythingAndPages(): void
    {
        [$device, $port] = $this->portOn();
        foreach ([1, 2, 3] as $last) {
            Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => "02b1b1b1b00$last", 'ipv4_address' => "192.0.2.$last"]);
        }

        $this->results(['q' => '', 'filter' => ['device_id' => ['eq' => $device->device_id]], 'per_page' => 10])
            ->assertJson(['mode' => 'list', 'total' => 3]);
        $this->assertGreaterThanOrEqual(3, $this->results([])->json('total'));
    }

    public function testFiltersNarrowTheList(): void
    {
        [$first, $firstPort] = $this->portOn('first-port');
        [$second, $secondPort] = $this->portOn('second-port');
        Ipv4Mac::factory()->create(['device_id' => $first->device_id, 'port_id' => $firstPort->port_id, 'mac_address' => '02b2b2b2b001', 'ipv4_address' => '192.0.2.61']);
        Ipv4Mac::factory()->create(['device_id' => $second->device_id, 'port_id' => $secondPort->port_id, 'mac_address' => '02b2b2b2b002', 'ipv4_address' => '192.0.2.62']);
        PortsFdb::factory()->create(['device_id' => $first->device_id, 'port_id' => $firstPort->port_id, 'mac_address' => '02b2b2b2b001', 'vlan' => 342]);
        PortsFdb::factory()->create(['device_id' => $second->device_id, 'port_id' => $secondPort->port_id, 'mac_address' => '02b2b2b2b003']);

        $q = '02:b2:b2';
        $this->results(['q' => $q])->assertJson(['total' => 3]);
        $this->results(['q' => $q, 'filter' => ['device_id' => ['eq' => $first->device_id]]])->assertJson(['total' => 1]);
        $this->results(['q' => $q, 'filter' => ['port_id' => ['eq' => $secondPort->port_id]]])->assertJson(['total' => 2]);
        $this->results(['q' => $q, 'filter' => ['ifAlias' => ['contains' => 'first']]])->assertJson(['total' => 1]);
        $this->results(['q' => $q, 'filter' => ['vlan' => ['eq' => 342]]])->assertJson(['total' => 1]);
        $this->results(['q' => $q, 'filter' => ['source' => ['eq' => 'fdb']]])->assertJson(['total' => 2]);
        $this->results(['q' => $q, 'filter' => ['source' => ['in' => 'neighbor,fdb']]])->assertJson(['total' => 3]);
        $this->results(['q' => $q, 'filter' => ['source' => ['neq' => 'neighbor']]])->assertJson(['total' => 2]);
    }

    public function testFiltersTurnAnExactSearchIntoAListUnlessOpeningTheHost(): void
    {
        [$device, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b3b3b3b001', 'ipv4_address' => '192.0.2.71']);

        $this->results(['q' => '192.0.2.71', 'filter' => ['source' => ['eq' => 'neighbor']]])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->results(['q' => '192.0.2.71', 'filter' => ['source' => ['eq' => 'fdb']]])
            ->assertJson(['mode' => 'not_found', 'total' => 0])
            ->assertJsonPath('html', fn ($html) => str_contains($html, __('search.not_found.without_filters')) && str_contains($html, __('search.not_found.filtered')) && ! str_contains($html, __('search.not_found.title')));
        $this->results(['q' => '192.0.2.71', 'view' => 'host', 'filter' => ['source' => ['eq' => 'fdb']]])->assertJsonPath('mode', 'host');
    }

    public function testDeviceAndPortFiltersFindHostsInTheForwardingTable(): void
    {
        [$router, , $switch, $switchPort] = $this->hostBehindSwitch('10.90.1.5', '02d0d0d0d001');
        [, , $otherSwitch] = $this->hostBehindSwitch('10.90.1.6', '02d0d0d0d002');

        $this->results(['q' => '', 'filter' => ['device_id' => ['eq' => $switch->device_id]]])->assertJson(['total' => 1]);
        $this->results(['q' => '', 'filter' => ['port_id' => ['eq' => $switchPort->port_id]]])->assertJson(['total' => 1]);
        $this->results(['q' => '10.90.1.0/24', 'filter' => ['device_id' => ['eq' => $switch->device_id]]])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->results(['q' => '10.90.1.0/24', 'filter' => ['device_id' => ['eq' => $otherSwitch->device_id]]])->assertJson(['total' => 1]);
        // the router that learned the IP still finds it
        $this->results(['q' => '', 'filter' => ['device_id' => ['eq' => $router->device_id]]])->assertJson(['total' => 1]);
    }

    public function testFdbSourceIncludesMacsWithAnIp(): void
    {
        $this->hostBehindSwitch('10.91.1.5', '02d1d1d1d001');

        $this->results(['q' => '02:d1:d1', 'filter' => ['source' => ['eq' => 'fdb']]])->assertJson(['total' => 1]);
        // the forwarding table has no IPs, it finds them through the neighbor tables
        $response = $this->results(['q' => '10.91.1.0/24', 'filter' => ['source' => ['eq' => 'fdb']]])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->assertStringContainsString('10.91.1.5', $response->json('html'));
    }

    public function testPastedListKeepsItsOrderAndCountsNotSeen(): void
    {
        $this->hostBehindSwitch('10.92.1.5', '02d2d2d2d001');
        $this->hostBehindSwitch('10.92.1.6', '02d2d2d2d002');

        $response = $this->results(['q' => '10.92.1.6 10.92.1.99 10.92.1.5'])->assertJson(['mode' => 'list', 'total' => 3]);

        $this->assertSame(['10.92.1.6', '10.92.1.99', '10.92.1.5'], $this->listedIps($response->json('html')));
        $this->assertStringContainsString(__('search.list.not_seen', ['count' => 1]), $response->json('summary'));

        // sorting by a column replaces the pasted order
        $sorted = $this->results(['q' => '10.92.1.6 10.92.1.99 10.92.1.5', 'sort' => 'ip']);
        $this->assertSame(['10.92.1.5', '10.92.1.6', '10.92.1.99'], $this->listedIps($sorted->json('html')));
    }

    public function testPastedMacWithAnIpIsListedOnce(): void
    {
        $this->hostBehindSwitch('10.93.1.5', '02d3d3d3d001');

        $this->results(['q' => '02:d3:d3:d3:d0:01 10.93.1.5'])->assertJson(['total' => 1]);
    }

    public function testLongPastedListSaysWhatWasNotSearched(): void
    {
        $list = implode(' ', array_map(fn ($last) => "192.0.2.$last", range(1, 60)));

        $response = $this->results(['q' => $list])->assertJson(['total' => 50]);

        $this->assertStringContainsString(__('search.list.terms_ignored', ['count' => 50, 'total' => 60]), $response->json('summary'));
    }

    public function testNetworkSearchCountsAddressesSeen(): void
    {
        $this->hostBehindSwitch('10.94.1.5', '02d4d4d4d001');

        $this->assertSame(__('search.list.network_seen', ['count' => 1, 'size' => '254']), $this->results(['q' => '10.94.1.0/24'])->json('summary'));
    }

    public function testMacSearchHasOneRowForEachMac(): void
    {
        [$router, $port] = $this->portOn();
        foreach ([5, 6, 7] as $last) {
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d5d5d5d001', 'ipv4_address' => "10.95.1.$last"]);
        }

        $response = $this->results(['q' => '02:d5:d5'])->assertJson(['mode' => 'list', 'total' => 1]);

        $this->assertStringContainsString(__('search.host.more', ['count' => 2]), $response->json('html'));
    }

    public function testListSortsByLastSeenWithUnknownLast(): void
    {
        [$router, $port] = $this->portOn();
        foreach (['old' => 5, 'new' => 6, 'unknown' => 7] as $age => $last) {
            $mac = "02d6d6d6d00$last";
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'ipv4_address' => "10.96.1.$last"]);
            if ($age !== 'unknown') {
                $entry = PortsFdb::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac]);
                PortsFdb::whereKey($entry->getKey())->toBase()->update(['updated_at' => $age === 'old' ? now()->subDays(9) : now()]);
            }
        }

        $oldest = $this->results(['q' => '10.96.1.0/24', 'sort' => 'seen', 'dir' => 'asc']);
        $this->assertSame(['10.96.1.5', '10.96.1.6', '10.96.1.7'], $this->listedIps($oldest->json('html')));
        $newest = $this->results(['q' => '10.96.1.0/24', 'sort' => 'seen', 'dir' => 'desc']);
        $this->assertSame(['10.96.1.6', '10.96.1.5', '10.96.1.7'], $this->listedIps($newest->json('html')));
    }

    public function testListUsesStandardPageLinksToTheSearchPage(): void
    {
        [$router, $port] = $this->portOn();
        foreach (range(1, 12) as $last) {
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => sprintf('02d7d7d7d0%02d', $last), 'ipv4_address' => "10.97.1.$last"]);
        }

        $response = $this->results(['q' => '10.97.1.0/24', 'per_page' => 10, 'sort' => 'mac', 'dir' => 'desc', 'page' => 2]);

        $this->assertCount(2, $this->listedIps($response->json('html')));
        $this->assertStringContainsString(e(route('search', ['q' => '10.97.1.0/24', 'per_page' => 10, 'sort' => 'mac', 'dir' => 'desc', 'page' => 1])), $response->json('html'));
    }

    public function testListShowsTheNeighborInterfaceWithoutAForwardingEntry(): void
    {
        [$router, $port] = $this->portOn('edge-link');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d8d8d8d001', 'ipv4_address' => '10.98.1.5']);

        $html = $this->results(['q' => '10.98.1.0/24'])->json('html');

        $this->assertStringContainsString(e(__('search.list.via_help')), $html);
        $this->assertStringContainsString('edge-link', $html);
    }

    public function testListShowsWhereAMonitoredHostIsPluggedIn(): void
    {
        [, , , $switchPort] = $this->hostBehindSwitch('10.99.1.5', '02d9d9d9d001');
        [, $own] = $this->portOn('own-interface');
        Ipv4Address::factory()->create(['port_id' => $own->port_id, 'ipv4_address' => '10.99.1.5', 'ipv4_prefixlen' => 24]);

        $html = $this->results(['q' => '10.99.1.0/24'])->json('html');

        $this->assertStringContainsString('own-interface', $html);
        $this->assertStringContainsString($switchPort->getLabel(), $html);
    }

    public function testVendorSearch(): void
    {
        LibrenmsConfig::set('mac_oui.enabled', true);
        DB::table('vendor_ouis')->insert(['oui' => 'f0f0f0', 'vendor' => 'Searchtest Widgets Inc']);
        [$device, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => 'f0f0f0123456', 'ipv4_address' => '192.0.2.44']);

        $this->results(['q' => 'searchtest widgets'])
            ->assertJson(['mode' => 'list', 'total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Searchtest Widgets Inc'));
    }

    public function testDevicesAreFoundByText(): void
    {
        $device = Device::factory()->create(['hostname' => 'search-core-sw1.example.com', 'ip' => '198.51.100.20']);

        $this->results(['q' => 'search-core'])
            ->assertJson(['mode' => 'list', 'total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, '198.51.100.20'));
        $this->results(['q' => 'search-core', 'filter' => ['source' => ['eq' => 'neighbor']]])->assertJson(['total' => 0]);
        $this->assertSame($device->device_id, Device::where('hostname', 'search-core-sw1.example.com')->value('device_id'));
    }

    public function testPastedAddressesListEachHostIncludingNotSeen(): void
    {
        [$device, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02b4b4b4b001', 'ipv4_address' => '192.0.2.81']);

        $response = $this->results(['q' => "192.0.2.81\n192.0.2.82"])->assertJson(['mode' => 'list', 'total' => 2]);

        $this->assertStringContainsString(__('search.host.not_seen'), $response->json('html'));
    }

    public function testLongPastedListIsAccepted(): void
    {
        $list = implode(' ', array_map(fn ($last) => "192.0.2.$last", range(1, 40)));
        $this->assertGreaterThan(255, strlen($list));

        $this->results(['q' => $list])->assertJson(['mode' => 'list', 'total' => 40]);
    }

    public function testListsRespectAccess(): void
    {
        [$visible, $visiblePort] = $this->portOn();
        [$hidden, $hiddenPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $visible->device_id, 'port_id' => $visiblePort->port_id, 'mac_address' => '02b5b5b5b001', 'ipv4_address' => '192.0.2.91']);
        Ipv4Mac::factory()->create(['device_id' => $hidden->device_id, 'port_id' => $hiddenPort->port_id, 'mac_address' => '02b5b5b5b002', 'ipv4_address' => '192.0.2.92']);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($visible->device_id);

        $this->results(['q' => '192.0.2.'], $user)->assertJson(['total' => 1]);
        $this->results(['q' => '192.0.2.92'], $user)->assertJsonPath('mode', 'not_found');
    }

    public function testVlanFilterIgnoresFdbEntriesTheUserCannotAccess(): void
    {
        [$visible, $visiblePort] = $this->portOn();
        [$hidden, $hiddenPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $visible->device_id, 'port_id' => $visiblePort->port_id, 'mac_address' => '02b6b6b6b001', 'ipv4_address' => '192.0.2.93']);
        PortsFdb::factory()->create(['device_id' => $hidden->device_id, 'port_id' => $hiddenPort->port_id, 'mac_address' => '02b6b6b6b001', 'vlan' => 342]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($visible->device_id);

        $this->results(['q' => '192.0.2.'], $user)->assertJson(['total' => 1]);
        $this->results(['q' => '192.0.2.', 'filter' => ['vlan' => ['eq' => 342]]], $user)->assertJsonMissing(['total' => 1]);
    }

    public function testRowsThatTieHaveAStableOrder(): void
    {
        [$device, $port] = $this->portOn();
        foreach (['02e2e2e20003', '02e2e2e20001', '02e2e2e20002'] as $mac) {
            $entry = PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac]);
            PortsFdb::whereKey($entry->getKey())->toBase()->update(['updated_at' => now()->subHour()->startOfHour()]);
        }

        $html = $this->results(['q' => '02:e2:e2', 'sort' => 'seen'])->json('html');

        preg_match_all("/highlightMac\\('([^']+)'\\)/", $html, $macs);
        $this->assertSame(['02e2e2e20001', '02e2e2e20002', '02e2e2e20003'], $macs[1]);
    }

    public function testPastedMacsCountThoseNotSeen(): void
    {
        [$router, $port] = $this->portOn();
        foreach ([5, 6] as $last) {
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e4e4e4e001', 'ipv4_address' => "10.73.1.$last"]);
        }

        $summary = $this->results(['q' => '02:e4:e4:e4:e0:01 02:e4:e4:e4:e0:02'])->assertJson(['total' => 2])->json('summary');

        $this->assertStringContainsString(__('search.list.not_seen', ['count' => 1]), $summary);
    }

    public function testWildcardsInASearchAreLiteral(): void
    {
        Device::factory()->create(['hostname' => 'swx1-wildcard', 'ip' => '198.51.100.24']);

        $this->results(['q' => 'swx1-wildcard'])->assertJson(['total' => 1]);
        $this->results(['q' => 'sw_1-wildcard'])->assertJsonPath('mode', 'not_found');
        $this->results(['q' => '%'])->assertJsonPath('mode', 'not_found');
    }

    public function testVlanFilterSupportsTheFilterOperators(): void
    {
        [$device, $port] = $this->portOn();
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e8e8e8e001', 'vlan' => 342]);
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e8e8e8e002', 'vlan' => 343]);

        foreach (['in' => '342', 'eq' => '342', 'lt' => '343'] as $operator => $value) {
            $this->results(['q' => '02:e8:e8', 'filter' => ['vlan' => [$operator => $value]]])->assertJson(['total' => 1]);
        }
    }

    public function testNetworkSearchStaysInItsAddressFamily(): void
    {
        Device::factory()->create(['hostname' => 'v6-family.example.test', 'ip' => '2001:db8::99']);
        Device::factory()->create(['hostname' => 'v4-family.example.test', 'ip' => '32.1.13.184']);

        $this->results(['q' => '32.1.13.0/24'])->assertJson(['mode' => 'list', 'total' => 1]);
        // the same leading bytes as 2001:db8::99
        $this->results(['q' => '32.1.0.0/16'])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->assertStringNotContainsString('2001:db8::99', $this->results(['q' => '32.1.0.0/16'])->json('html'));
        $this->assertStringNotContainsString('32.1.13.184', $this->results(['q' => '2001::/16'])->json('html'));
    }

    public function testNotSeenCountHandlesARowWithoutAnAddress(): void
    {
        $device = Device::factory()->create(['hostname' => '10.71.1.5']);
        Device::whereKey($device->device_id)->toBase()->update(['ip' => '']);

        // the device's row has no IP or MAC to list it by, both pasted addresses are unknown
        $summary = $this->results(['q' => '10.71.1.5 10.71.1.6'])->json('summary');

        $this->assertStringContainsString(__('search.list.not_seen', ['count' => 2]), $summary);
    }

    public function testPastedMacsAndIpsKeepTheirOrder(): void
    {
        $this->hostBehindSwitch('10.68.1.5', '02e9e9e9e001');
        $this->hostBehindSwitch('10.68.1.6', '02e9e9e9e002');

        $response = $this->results(['q' => '10.68.1.6 02:e9:e9:e9:e0:01 10.68.1.99'])->assertJson(['total' => 3]);

        $this->assertSame(['10.68.1.6', '10.68.1.5', '10.68.1.99'], $this->listedIps($response->json('html')));
    }

    public function testVendorSearchFindsInterfaceMacs(): void
    {
        LibrenmsConfig::set('mac_oui.enabled', true);
        DB::table('vendor_ouis')->insert(['oui' => 'f0f0f2', 'vendor' => 'Interfacetest Widgets Inc']);
        [, $port] = $this->portOn();
        $port->update(['ifPhysAddress' => 'f0f0f2123456']);

        $this->results(['q' => 'interfacetest widgets', 'filter' => ['source' => ['eq' => 'interface']]])->assertJson(['mode' => 'list', 'total' => 1]);
    }
}
