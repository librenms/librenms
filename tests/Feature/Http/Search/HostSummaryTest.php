<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\NeighborEntry;
use App\Http\Controllers\Search\HostLookup;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Link;
use App\Models\PortsFdb;
use App\Models\Vlan;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Dns;
use LibreNMS\Util\IP;

class HostSummaryTest extends SearchTestCase
{
    public function testExactIpShowsTheHost(): void
    {
        [$router, $routerPort] = $this->portOn();
        [$switch, $access] = $this->portOn('access-port-7');
        [, $uplink] = $this->portOn('uplink-port');
        Link::factory()->create(['local_port_id' => $uplink->port_id, 'local_device_id' => $uplink->device_id]);

        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02aabbccddee', 'ipv4_address' => '192.0.2.77']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $access->port_id, 'mac_address' => '02aabbccddee', 'vlan_id' => 0]);
        PortsFdb::factory()->create(['device_id' => $uplink->device_id, 'port_id' => $uplink->port_id, 'mac_address' => '02aabbccddee', 'vlan_id' => 0]);
        Ipv4Address::factory()->create(['port_id' => $routerPort->port_id, 'ipv4_address' => '192.0.2.1', 'ipv4_prefixlen' => 24]);

        foreach (['192.0.2.77', '02:aa:bb:cc:dd:ee', 'ip="192.0.2.77"'] as $query) {
            $html = $this->results(['q' => $query])->assertJsonPath('mode', 'host')->json('html');

            $this->assertMatchesRegularExpression('/' . __('search.host.switch_port') . '.*access-port-7.*' . __('search.host.also_learned') . '.*uplink-port/s', $html);
            $this->assertStringContainsString('192.0.2.77', $html);
            $this->assertStringContainsString('192.0.2.1/24', $html); // on the same subnet
            $this->assertStringContainsString("highlightMac('02aabbccddee')", $html);
        }
    }

    public function testIpOwnedByAnInterfaceShowsTheInterface(): void
    {
        [, $port] = $this->portOn('core-svi-120');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.7', 'ipv4_prefixlen' => 24]);

        $html = $this->results(['q' => '198.51.100.7'])->assertJsonPath('mode', 'host')->json('html');

        $this->assertStringContainsString(__('search.host.interface'), $html);
        $this->assertStringContainsString('core-svi-120', $html);
    }

    public function testUnknownAddressIsNotSeen(): void
    {
        $html = $this->results(['q' => '203.0.113.200'])->assertJson(['mode' => 'not_found', 'total' => 0])->json('html');

        $this->assertStringContainsString('203.0.113.200', $html);
        $this->assertStringContainsString(__('search.not_found.title'), $html);
        $this->assertStringContainsString(__('search.not_found.still_possible', [], 'en'), html_entity_decode($html, ENT_QUOTES));
    }

    public function testPartialIpv6WithDoubleColonFallsBackToTheList(): void
    {
        [$device, $port] = $this->portOn();
        Ipv6Nd::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '021122334455', 'ipv6_address' => '2001:0db8:0000:0000:0011:22ff:fe33:4455']);

        $this->results(['q' => '2001:db8::11:22ff'])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->results(['q' => '2001:db8::11:22ff:fe33:4455'])->assertJsonPath('mode', 'host');
    }

    public function testSeveralMacsForAnAddressShowsBoth(): void
    {
        [$router, $port] = $this->portOn();
        [$other, $otherPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0001', 'ipv4_address' => '192.0.2.31']);
        Ipv4Mac::factory()->create(['device_id' => $other->device_id, 'port_id' => $otherPort->port_id, 'mac_address' => '02c0ffee0002', 'ipv4_address' => '192.0.2.31']);

        $html = $this->results(['q' => '192.0.2.31'])->json('html');

        $this->assertStringContainsString(e(__('search.host.several_macs_help')), $html);
        $this->assertStringContainsString("highlightMac('02c0ffee0001')", $html);
        $this->assertStringContainsString("highlightMac('02c0ffee0002')", $html);
    }

    public function testSeveralMacsDimsMacNotSeenRecently(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0011', 'ipv4_address' => '192.0.2.41']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0012', 'ipv4_address' => '192.0.2.41']);
        PortsFdb::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0011']);
        $stale = PortsFdb::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0012']);
        PortsFdb::whereKey($stale->getKey())->toBase()->update(['updated_at' => now()->subDays(11)]);

        $host = (new HostLookup($this->admin()))
            ->find([new HostKey([IP::parse('192.0.2.41')])])[0];

        $this->assertTrue($host->severalMacs);
        $this->assertTrue($host->isStale('02c0ffee0012'));
        $this->assertFalse($host->isStale('02c0ffee0011'));
        $this->assertSame('02c0ffee0011', $host->bestLocation()?->mac);
    }

    public function testDifferentContextsAndLinkLocalAreNotSeveralMacs(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0021', 'ipv4_address' => '192.0.2.51', 'context_name' => 'red']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0022', 'ipv4_address' => '192.0.2.51', 'context_name' => 'blue']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0023', 'ipv6_address' => 'fe80:0000:0000:0000:0000:0000:0000:0051']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0024', 'ipv6_address' => 'fe80:0000:0000:0000:0000:0000:0000:0051']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '', 'ipv6_address' => '2001:0db8:0000:0000:0000:0000:0000:0051']);

        foreach (['192.0.2.51', 'fe80::51'] as $query) {
            $this->assertStringNotContainsString(e(__('search.host.several_macs_help')), $this->results(['q' => $query])->json('html'));
        }
    }

    public function testAggregateAndUplinkPortsAreListedAsSeenOn(): void
    {
        [$router, $routerPort] = $this->portOn();
        [, $lagOne] = $this->portOn('lag-one');
        [, $lagTwo] = $this->portOn('lag-two');
        $lagOne->update(['ifType' => 'ieee8023adLag']);
        $lagTwo->update(['ifType' => 'ieee8023adLag']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d001', 'ipv4_address' => '192.0.2.101']);
        PortsFdb::factory()->create(['device_id' => $lagOne->device_id, 'port_id' => $lagOne->port_id, 'mac_address' => '02d0d0d0d001', 'vlan_id' => 0]);
        PortsFdb::factory()->create(['device_id' => $lagTwo->device_id, 'port_id' => $lagTwo->port_id, 'mac_address' => '02d0d0d0d001', 'vlan_id' => 0]);

        $html = $this->results(['q' => '192.0.2.101'])->json('html');

        $this->assertStringContainsString(__('search.host.seen_on'), $html);
        $this->assertStringNotContainsString(__('search.host.switch_port'), $html);
        $this->assertStringNotContainsString(__('search.host.also_learned'), $html);
        $this->assertStringContainsString('lag-one', $html);
        $this->assertStringContainsString('lag-two', $html);
    }

    public function testOrdinaryPortBeatsAggregateWithFewerMacs(): void
    {
        [$router, $routerPort] = $this->portOn();
        [, $lag] = $this->portOn('lag-port');
        [, $access] = $this->portOn('plain-port');
        $lag->update(['ifType' => 'ieee8023adLag']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d002', 'ipv4_address' => '192.0.2.102']);
        PortsFdb::factory()->create(['device_id' => $lag->device_id, 'port_id' => $lag->port_id, 'mac_address' => '02d0d0d0d002', 'vlan_id' => 0]);
        PortsFdb::factory()->create(['device_id' => $access->device_id, 'port_id' => $access->port_id, 'mac_address' => '02d0d0d0d002', 'vlan_id' => 0]);
        PortsFdb::factory()->create(['device_id' => $access->device_id, 'port_id' => $access->port_id, 'mac_address' => '02d0d0d0d003', 'vlan_id' => 0]);

        $html = $this->results(['q' => '192.0.2.102'])->json('html');

        $this->assertMatchesRegularExpression('/' . __('search.host.switch_port') . '.*plain-port.*' . __('search.host.also_learned') . '.*lag-port/s', $html);
    }

    public function testSamePortInTwoVlansIsListedOnce(): void
    {
        [$router, $routerPort] = $this->portOn();
        [$switch, $port] = $this->portOn('twice-port');
        [, $other] = $this->portOn('other-port');
        $vlan = Vlan::factory()->create(['device_id' => $switch->device_id, 'vlan_vlan' => 77]);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d004', 'ipv4_address' => '192.0.2.103']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d0d0d0d004', 'vlan_id' => 0]);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d0d0d0d004', 'vlan_id' => $vlan->vlan_id]);
        PortsFdb::factory()->create(['device_id' => $other->device_id, 'port_id' => $other->port_id, 'mac_address' => '02d0d0d0d004', 'vlan_id' => 0]);

        $host = (new HostLookup($this->admin()))->find([new HostKey([IP::parse('192.0.2.103')])])[0];

        $this->assertCount(2, $host->currentLocations());
        $this->assertSame($vlan->vlan_id, collect($host->currentLocations())->firstWhere('portId', $port->port_id)->vlan?->vlan_id);
    }

    public function testNeighborInterfaceWithArpAndNdIsOneRow(): void
    {
        [$router, $port] = $this->portOn('both-tables');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e0e0e0e002', 'ipv4_address' => '192.0.2.104']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e0e0e0e002', 'ipv6_address' => '2001:0db8:0000:0000:0000:0000:0000:0104']);

        $host = (new HostLookup($this->admin()))->find([new HostKey([IP::parse('192.0.2.104')])], follow: true)[0];

        $this->assertCount(1, $host->neighborInterfaces());
        $this->assertSame(['ARP', 'ND'], array_column($host->neighborInterfaces()[0], 'source'));
    }

    public function testNotFoundOffersUsefulSearches(): void
    {
        LibrenmsConfig::set('mac_oui.enabled', true);
        DB::table('vendor_ouis')->insert(['oui' => 'f0f0f1', 'vendor' => 'Notfound Widgets Inc']);
        [, $port] = $this->portOn('peer-port');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '10.79.1.2', 'ipv4_prefixlen' => 24]);

        $byIp = $this->results(['q' => '10.79.1.50'])->assertJsonPath('mode', 'not_found')->json('html');
        $this->assertStringContainsString(__('search.not_found.search_network', ['network' => '10.79.1.0/24']), $byIp);
        $this->assertStringContainsString('peer-port', $byIp);

        $byMac = $this->results(['q' => 'f0:f0:f1:00:00:01'])->assertJsonPath('mode', 'not_found')->json('html');
        $this->assertStringContainsString('Notfound Widgets Inc', $byMac);
        $this->assertStringContainsString(__('search.not_found.search_vendor_prefix', ['prefix' => 'f0:f0:f1']), $byMac);

        $byText = $this->results(['q' => 'no-such-thing-here'])->assertJson(['mode' => 'not_found', 'total' => 0])->json('html');
        $this->assertStringContainsString('no-such-thing-here', $byText);
        $this->assertStringNotContainsString(__('search.not_found.without_filters'), $byText);
    }

    public function testLocationsHaveTheirPortsDevice(): void
    {
        $this->hostBehindSwitch('192.0.2.105', '02e0e0e0e005');

        $host = (new HostLookup($this->admin()))->find([new HostKey([IP::parse('192.0.2.105')])])[0];

        $this->assertTrue($host->locations[0]->port?->relationLoaded('device'));
    }

    public function testHostWithManyAddressesLeavesEntriesForOtherHosts(): void
    {
        [$router, $port] = $this->portOn();
        foreach (array_chunk(range(1, 2000), 500) as $chunk) {
            Ipv4Mac::insert(array_map(fn (int $i): array => ['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e1e1e1e001', 'ipv4_address' => '10.75.' . intdiv($i, 256) . '.' . ($i % 256), 'context_name' => ''], $chunk));
        }
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e1e1e1e002', 'ipv4_address' => '192.0.2.106']);

        $hosts = (new HostLookup($this->admin()))->find([new HostKey(macs: ['02e1e1e1e001']), new HostKey([IP::parse('192.0.2.106')])]);

        $this->assertCount(2000, $hosts[0]->entries);
        $this->assertCount(1, $hosts[1]->entries);
    }

    public function testResolvedHostnameOfAKnownDeviceIsListed(): void
    {
        Device::factory()->create(['hostname' => 'dns-elsewhere.example.test', 'ip' => '198.51.100.21']);
        $this->mock(Dns::class, fn ($dns) => $dns->shouldReceive('getAddresses')->andReturn(['203.0.113.77']));

        $this->results(['q' => 'dns-elsewhere.example.test'])
            ->assertJson(['mode' => 'list', 'total' => 1])
            ->assertJsonPath('html', fn ($html) => str_contains($html, '198.51.100.21'));
    }

    public function testResolvedHostnameNothingKnowsSaysWhatItResolvedTo(): void
    {
        $this->mock(Dns::class, fn ($dns) => $dns->shouldReceive('getAddresses')->andReturn(['203.0.113.78']));

        $html = $this->results(['q' => 'nothing-knows.example.test'])->assertJsonPath('mode', 'not_found')->json('html');

        $this->assertStringContainsString(__('search.not_found.resolves_to'), $html);
        $this->assertStringContainsString('203.0.113.78', $html);
    }

    public function testBareHexSerialFindsTheDevice(): void
    {
        Device::factory()->create(['serial' => '123456789012', 'ip' => '198.51.100.22']);

        $this->results(['q' => '123456789012'])->assertJson(['mode' => 'list', 'total' => 1]);
        $this->results(['q' => '12:34:56:78:90:12'])->assertJsonPath('mode', 'not_found');
        $this->results(['q' => 'aabbccddeeff'])->assertJsonPath('mode', 'not_found');
    }

    public function testFollowingAMacWithManyAddressesKeepsTheSearchedIp(): void
    {
        [$router, $port] = $this->portOn();
        foreach (array_chunk(range(1, 2000), 500) as $chunk) {
            Ipv4Mac::insert(array_map(fn (int $i): array => ['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e6e6e6e001', 'ipv4_address' => '10.70.' . intdiv($i, 256) . '.' . ($i % 256), 'context_name' => ''], $chunk));
        }
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e6e6e6e001', 'ipv4_address' => '10.70.200.1']);

        $host = (new HostLookup($this->admin()))->find([new HostKey([IP::parse('10.70.200.1')])], follow: true)[0];

        $this->assertFalse($host->isNotSeen());
        $this->assertContains('10.70.200.1', array_map(fn (NeighborEntry $entry): string => $entry->ip->compressed(), $host->entries));
    }
}
