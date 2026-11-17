<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\NeighborEntry;
use App\Http\Controllers\Search\HostLookup;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\Link;
use App\Models\PortsFdb;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL as LaravelUrl;
use Illuminate\Support\Js;
use LibreNMS\Util\Dns;
use LibreNMS\Util\IP;
use LibreNMS\Util\Url;
use PHPUnit\Framework\Attributes\DataProvider;

class HostSummaryTest extends SearchTestCase
{
    /**
     * @return array<string, array{string, string, string, string, int}>
     */
    public static function multiAddressHosts(): array
    {
        return [
            'IPv4' => ['198.51.100.70', '203.0.113.70', '198.51.100.1', '203.0.113.1', 24],
            'IPv6' => ['2001:db8:1::70', '2001:db8:2::70', '2001:db8:1::1', '2001:db8:2::1', 64],
        ];
    }

    #[DataProvider('multiAddressHosts')]
    public function testExactIpScopesSubnetAndDnsToTheSearchedAddress(string $searched, string $other, string $peer, string $otherPeer, int $prefix): void
    {
        foreach ([[$searched, $peer], [$other, $otherPeer]] as [$ip, $routerIp]) {
            [$router, $port] = $this->portOn();
            if (str_contains($ip, ':')) {
                Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e7e7e7e001', 'ipv6_address' => IP::parse($ip)->uncompressed()]);
                Ipv6Address::factory()->create(['port_id' => $port->port_id, 'ipv6_address' => IP::parse($routerIp)->uncompressed(), 'ipv6_compressed' => $routerIp, 'ipv6_prefixlen' => $prefix]);
            } else {
                Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e7e7e7e001', 'ipv4_address' => $ip]);
                Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => $routerIp, 'ipv4_prefixlen' => $prefix]);
            }
        }
        $this->mock(Dns::class, function ($dns) use ($searched, $other): void {
            $dns->shouldReceive('reverseHostname')->once()->with($searched)->andReturn(null);
            $dns->shouldNotReceive('reverseHostname')->with($other);
        });

        $response = $this->results(['q' => $searched])->assertJsonPath('mode', 'host');

        $this->assertStringContainsString("$peer/$prefix", $response->json('html'));
        $this->assertStringNotContainsString("$otherPeer/$prefix", $response->json('html'));
        $this->assertStringContainsString($other, $response->json('html'));
        $nameUrl = LaravelUrl::signedRoute('search.name', ['ips' => [$searched]]);
        $this->assertStringContainsString((string) Js::from($nameUrl), $response->json('heading'));
        $this->assertStringContainsString('x-text="confirmedName ??', $response->json('heading'));
        $this->getJson($nameUrl)->assertOk()->assertJsonPath('name', null);

        $macResponse = $this->results(['q' => '02:e7:e7:e7:e0:01'])->assertJsonPath('mode', 'host');
        $this->assertStringContainsString("$peer/$prefix", $macResponse->json('html'));
        $this->assertStringContainsString("$otherPeer/$prefix", $macResponse->json('html'));
        $this->assertStringContainsString(urlencode($other), $macResponse->json('heading'));
    }

    public function testExactIpShowsTheHost(): void
    {
        [$router, $routerPort] = $this->portOn();
        [$switch, $access] = $this->portOn('access-port-7');
        [, $uplink] = $this->portOn('uplink-port');
        Link::factory()->create(['local_port_id' => $uplink->port_id, 'local_device_id' => $uplink->device_id]);

        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02aabbccddee', 'ipv4_address' => '192.0.2.77']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $access->port_id, 'mac_address' => '02aabbccddee', 'vlan' => null]);
        PortsFdb::factory()->create(['device_id' => $uplink->device_id, 'port_id' => $uplink->port_id, 'mac_address' => '02aabbccddee', 'vlan' => null]);
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

        $response = $this->results(['q' => '192.0.2.31']);
        $html = $response->json('html');

        $this->assertStringContainsString(e(__('search.host.several_macs_help')), $response->json('heading'));
        $this->assertStringContainsString("highlightMac('02c0ffee0001')", $html);
        $this->assertStringContainsString("highlightMac('02c0ffee0002')", $html);
    }

    public function testSeveralMacsSaysWhichMacEachLocationIsFor(): void
    {
        [$router, $port] = $this->portOn();
        [$switch, $switchPort] = $this->portOn('access-port');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0031', 'ipv4_address' => '192.0.2.51']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0032', 'ipv4_address' => '192.0.2.51']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $switchPort->port_id, 'mac_address' => '02c0ffee0031']);

        $html = $this->results(['q' => '192.0.2.51'])->json('html');

        $this->assertStringContainsString("formatMac('02c0ffee0031')\"></div>", $html);
        $this->assertStringContainsString("formatMac('02c0ffee0032')\"></div>", $html);
        $this->assertStringContainsString(__('search.host.no_forwarding_entry'), $html);
    }

    public function testSeveralMacsDimsMacNotSeenRecently(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0011', 'ipv4_address' => '192.0.2.41']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0012', 'ipv4_address' => '192.0.2.41']);
        PortsFdb::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0011']);
        $stale = PortsFdb::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02c0ffee0012']);
        PortsFdb::whereKey($stale->getKey())->toBase()->update(['updated_at' => now()->subDays(11)]);

        $host = new HostLookup($this->admin())
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
        PortsFdb::factory()->create(['device_id' => $lagOne->device_id, 'port_id' => $lagOne->port_id, 'mac_address' => '02d0d0d0d001', 'vlan' => null]);
        PortsFdb::factory()->create(['device_id' => $lagTwo->device_id, 'port_id' => $lagTwo->port_id, 'mac_address' => '02d0d0d0d001', 'vlan' => null]);

        $html = $this->results(['q' => '192.0.2.101'])->json('html');

        $this->assertStringContainsString(__('search.host.seen_on'), $html);
        $this->assertStringNotContainsString(__('search.host.switch_port'), $html);
        $this->assertStringNotContainsString(__('search.host.also_learned'), $html);
        $this->assertStringContainsString('lag-one', $html);
        $this->assertStringContainsString('lag-two', $html);
    }

    public function testMoreLocationsLinkToTableOfEveryLocation(): void
    {
        [$router, $routerPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d004', 'ipv4_address' => '192.0.2.104']);
        foreach (range(1, 7) as $number) {
            [, $lag] = $this->portOn("lag-$number");
            $lag->update(['ifType' => 'ieee8023adLag']);
            PortsFdb::factory()->create(['device_id' => $lag->device_id, 'port_id' => $lag->port_id, 'mac_address' => '02d0d0d0d004', 'vlan' => null]);
        }

        $html = $this->results(['q' => '192.0.2.104'])->json('html');
        $this->assertStringContainsString(e(route('search', ['q' => '192.0.2.104', 'view' => 'locations'])), $html);
        $this->assertStringContainsString(__('search.host.more', ['count' => 2]), $html);

        $table = $this->results(['q' => '192.0.2.104', 'view' => 'locations'])->assertJsonPath('mode', 'host')->json('html');
        foreach (range(1, 7) as $number) {
            $this->assertStringContainsString("lag-$number", $table);
        }
        $this->assertSame(8, substr_count($table, '<tr'));
        $this->assertStringNotContainsString(__('search.host.seen_on'), $table);
        $this->assertStringContainsString(e(route('search', ['q' => '192.0.2.104', 'view' => 'host'])), $table);
    }

    public function testMoreOtherLocationsLinkToTableOfEveryLocation(): void
    {
        [$router, $routerPort] = $this->portOn();
        [, $access] = $this->portOn('plain-port');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d005', 'ipv4_address' => '192.0.2.105']);
        PortsFdb::factory()->create(['device_id' => $access->device_id, 'port_id' => $access->port_id, 'mac_address' => '02d0d0d0d005', 'vlan' => null]);
        foreach (range(1, 6) as $number) {
            [, $lag] = $this->portOn("lag-$number");
            $lag->update(['ifType' => 'ieee8023adLag']);
            PortsFdb::factory()->create(['device_id' => $lag->device_id, 'port_id' => $lag->port_id, 'mac_address' => '02d0d0d0d005', 'vlan' => null]);
        }

        $html = $this->results(['q' => '192.0.2.105'])->json('html');

        $this->assertStringContainsString(__('search.host.also_learned'), $html);
        $this->assertStringContainsString(__('search.host.more', ['count' => 1]), $html);
        $this->assertStringContainsString(e(route('search', ['q' => '192.0.2.105', 'view' => 'locations'])), $html);
    }

    public function testLocationsViewWithoutLocationsShowsTheSummary(): void
    {
        [$router, $routerPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d006', 'ipv4_address' => '192.0.2.106']);

        $html = $this->results(['q' => '192.0.2.106', 'view' => 'locations'])->json('html');

        $this->assertStringContainsString(__('search.host.no_forwarding_entry'), $html);
    }

    public function testMoreOnTheSubnetLinkToItsInterfaces(): void
    {
        foreach (range(1, 6) as $number) {
            [, $port] = $this->portOn("subnet-$number");
            Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => "10.85.1.$number", 'ipv4_prefixlen' => 24]);
        }
        [, $outside] = $this->portOn('outside');
        Ipv4Address::factory()->create(['port_id' => $outside->port_id, 'ipv4_address' => '10.85.2.1', 'ipv4_prefixlen' => 24]);
        $list = ['q' => '10.85.1.0/24', 'filter' => ['source' => ['eq' => 'interface']]];

        $html = $this->results(['q' => '10.85.1.1'])->json('html');
        $this->assertStringContainsString(e(route('search', $list)), $html);
        $this->assertStringContainsString(__('search.host.more', ['count' => 2]), $html);

        $listed = $this->results($list)->assertJsonPath('mode', 'list')->assertJsonPath('total', 6)->json('html');
        $this->assertStringContainsString('subnet-6', $listed);
        $this->assertStringNotContainsString('outside', $listed);
    }

    public function testOrdinaryPortBeatsAggregateWithFewerMacs(): void
    {
        [$router, $routerPort] = $this->portOn();
        [, $lag] = $this->portOn('lag-port');
        [, $access] = $this->portOn('plain-port');
        $lag->update(['ifType' => 'ieee8023adLag']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d002', 'ipv4_address' => '192.0.2.102']);
        PortsFdb::factory()->create(['device_id' => $lag->device_id, 'port_id' => $lag->port_id, 'mac_address' => '02d0d0d0d002', 'vlan' => null]);
        PortsFdb::factory()->create(['device_id' => $access->device_id, 'port_id' => $access->port_id, 'mac_address' => '02d0d0d0d002', 'vlan' => null]);
        PortsFdb::factory()->create(['device_id' => $access->device_id, 'port_id' => $access->port_id, 'mac_address' => '02d0d0d0d003', 'vlan' => null]);

        $html = $this->results(['q' => '192.0.2.102'])->json('html');

        $this->assertMatchesRegularExpression('/' . __('search.host.switch_port') . '.*plain-port.*' . __('search.host.also_learned') . '.*lag-port/s', $html);
    }

    public function testSamePortInTwoVlansIsListedOnce(): void
    {
        [$router, $routerPort] = $this->portOn();
        [$switch, $port] = $this->portOn('twice-port');
        [, $other] = $this->portOn('other-port');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02d0d0d0d004', 'ipv4_address' => '192.0.2.103']);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d0d0d0d004', 'vlan' => null]);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $port->port_id, 'mac_address' => '02d0d0d0d004', 'vlan' => 77]);
        PortsFdb::factory()->create(['device_id' => $other->device_id, 'port_id' => $other->port_id, 'mac_address' => '02d0d0d0d004', 'vlan' => null]);

        $host = new HostLookup($this->admin())->find([new HostKey([IP::parse('192.0.2.103')])])[0];

        $this->assertCount(2, $host->currentLocations());
        $this->assertSame(77, collect($host->currentLocations())->firstWhere('portId', $port->port_id)->vlan);
        $this->assertNull(collect($host->currentLocations())->firstWhere('portId', $other->port_id)->vlan);
    }

    public function testNeighborInterfaceWithArpAndNdIsOneRow(): void
    {
        [$router, $port] = $this->portOn('both-tables');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e0e0e0e002', 'ipv4_address' => '192.0.2.104']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e0e0e0e002', 'ipv6_address' => '2001:0db8:0000:0000:0000:0000:0000:0104']);

        $host = new HostLookup($this->admin())->find([new HostKey([IP::parse('192.0.2.104')])], follow: true)[0];

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

        $host = new HostLookup($this->admin())->find([new HostKey([IP::parse('192.0.2.105')])])[0];

        $this->assertTrue($host->locations[0]->port?->relationLoaded('device'));
    }

    public function testHostWithManyAddressesLeavesEntriesForOtherHosts(): void
    {
        [$router, $port] = $this->portOn();
        foreach (array_chunk(range(1, 2000), 500) as $chunk) {
            Ipv4Mac::insert(array_map(fn (int $i): array => ['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e1e1e1e001', 'ipv4_address' => '10.75.' . intdiv($i, 256) . '.' . ($i % 256), 'context_name' => ''], $chunk));
        }
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02e1e1e1e002', 'ipv4_address' => '192.0.2.106']);

        $hosts = new HostLookup($this->admin())->find([new HostKey(macs: ['02e1e1e1e001']), new HostKey([IP::parse('192.0.2.106')])]);

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

    public function testHostSummaryDoesNotWaitForReverseDns(): void
    {
        $this->hostBehindSwitch('198.51.100.30', '02f1f1f1f001');
        $this->mock(Dns::class, fn ($dns) => $dns->shouldNotReceive('reverseHostname'));

        $heading = $this->results(['q' => '198.51.100.30'])->assertJsonPath('mode', 'host')->json('heading');

        $this->assertStringContainsString('nameUrl:', $heading);
        $this->assertMatchesRegularExpression('/>\s*198\.51\.100\.30\s*<\/h2>/', $heading);
        $this->assertStringContainsString(route('device.add', ['hostname' => '198.51.100.30']), $heading);
        $this->assertStringContainsString('btn btn-default btn-xs', $heading);
        $this->assertStringContainsString('aria-label="' . __('search.host.add_device') . '"', $heading);
        $this->assertMatchesRegularExpression('/fa fa-plus.*<h2/s', $heading);
        $this->assertStringContainsString('class="tw:inline-flex tw:w-6 tw:shrink-0 tw:items-center tw:justify-center"', $heading);
        $this->assertStringNotContainsString('>' . __('search.host.add_device') . '</a>', $heading);
        $this->assertStringNotContainsString('fa fa-server', $heading);
        $this->assertStringContainsString(':href="addDeviceUrl"', $heading);
        $this->assertStringContainsString(':title="dnsDescription"', $heading);
        $this->assertStringContainsString(':aria-label="dnsDescription"', $heading);
        $this->assertStringContainsString(':aria-busy="dnsLoading"', $heading);
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">' . $heading);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//span[@role="img"]//i')->length);
    }

    public function testAddDeviceLinkRequiresCreatePermission(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02f1f1f1f003', 'ipv4_address' => '198.51.100.29']);
        $user = \App\Models\User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($router->device_id);

        $heading = $this->results(['q' => '198.51.100.29'], $user)->assertJsonPath('mode', 'host')->json('heading');

        $this->assertStringNotContainsString(route('device.add'), $heading);
    }

    public function testResolvedHostnameAlsoChecksReverseDns(): void
    {
        $this->hostBehindSwitch('198.51.100.31', '02f1f1f1f002');
        $this->mock(Dns::class, fn ($dns) => $dns->shouldReceive('getAddresses')->andReturn(['198.51.100.31']));

        $heading = $this->results(['q' => 'known-host.example.test'])->assertJsonPath('mode', 'host')->json('heading');

        $this->assertStringContainsString('known-host.example.test', $heading);
        $this->assertStringContainsString(e(route('device.add', ['hostname' => 'known-host.example.test'])), $heading);
        $this->assertStringContainsString('nameUrl:', $heading);
    }

    public function testMonitoredHostIsTitledByItsDeviceAndChecksDnsAsynchronously(): void
    {
        [$device, $port] = $this->portOn('core-svi-121');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.40', 'ipv4_prefixlen' => 24]);
        $this->mock(Dns::class, function ($dns): void {
            $dns->shouldNotReceive('getAddresses');
            $dns->shouldNotReceive('reverseHostname');
        });

        $heading = $this->results(['q' => '198.51.100.40'])->assertJsonPath('mode', 'host')->json('heading');

        $this->assertStringContainsString(Url::deviceUrl($device), $heading);
        $this->assertStringContainsString(e((string) $device->display), $heading);
        $this->assertStringContainsString('aria-label="' . __('search.host.monitored') . '"', $heading);
        $this->assertStringContainsString('title="This address belongs to a device monitored by LibreNMS."', $heading);
        $this->assertStringNotContainsString('>' . __('search.host.monitored') . '</span>', $heading);
        $this->assertMatchesRegularExpression('/fa fa-server.*<h2/s', $heading);
        $this->assertStringContainsString('class="tw:inline-flex tw:w-6 tw:shrink-0 tw:items-center tw:justify-center"', $heading);
        $this->assertStringNotContainsString(route('device.add'), $heading);
        $this->assertStringContainsString('nameUrl:', $heading);
    }

    public function testInterfacePanelOnlyAddsThePort(): void
    {
        [$device, $port] = $this->portOn('core-svi-123');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.43', 'ipv4_prefixlen' => 24]);

        $response = $this->results(['q' => '198.51.100.43']);

        $this->assertStringContainsString('core-svi-123', $response->json('html'));
        $this->assertStringNotContainsString(Url::deviceUrl($device) . '"', $response->json('html'));
    }

    public function testDeviceFoundByItsIpShowsWhereItIsPluggedIn(): void
    {
        [, $switchPort] = $this->portOn('uplink-to-managed');
        $device = Device::factory()->create(['ip' => '198.51.100.44']);
        [$router, $routerPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02f3f3f3f001', 'ipv4_address' => '198.51.100.44']);
        PortsFdb::factory()->create(['device_id' => $switchPort->device_id, 'port_id' => $switchPort->port_id, 'mac_address' => '02f3f3f3f001']);

        $response = $this->results(['q' => '198.51.100.44']);

        $this->assertStringContainsString(Url::deviceUrl($device), $response->json('heading'));
        $this->assertStringContainsString(__('search.host.switch_port'), $response->json('html'));
        $this->assertStringContainsString('uplink-to-managed', $response->json('html'));
    }

    public function testTitleIsTheDeviceTheInterfaceIsOn(): void
    {
        [$owner, $port] = $this->portOn('core-svi-122');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.42', 'ipv4_prefixlen' => 24]);
        $other = Device::factory()->create(['ip' => '198.51.100.42', 'display' => 'managed-by-this-ip']);

        $heading = $this->results(['q' => '198.51.100.42'])->json('heading');

        $this->assertStringContainsString(Url::deviceUrl($owner), $heading);
        $this->assertStringNotContainsString((string) $other->display, $heading);
    }

    public function testLoopbackOnADeviceDoesNotNameTheHost(): void
    {
        [$device, $port] = $this->portOn('lo');
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '127.0.0.53', 'ipv4_prefixlen' => 8]);

        $response = $this->results(['q' => '127.0.0.53'])->assertJsonPath('mode', 'host');

        $this->assertMatchesRegularExpression('/>\s*127\.0\.0\.53\s*<\/h2>/', $response->json('heading'));
        $this->assertStringContainsString(e((string) $device->display), $response->json('html'));
    }

    public function testLoopbackAndLinkLocalAddressesAreNotLookedUp(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02f2f2f2f001', 'ipv4_address' => '169.254.10.5']);
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02f2f2f2f002', 'ipv6_address' => 'fe80:0000:0000:0000:0000:0000:0000:0052']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02f2f2f2f003', 'ipv4_address' => '127.0.0.1']);

        foreach (['169.254.10.5', 'fe80::52', '127.0.0.1'] as $ip) {
            $heading = $this->results(['q' => $ip])->assertJsonPath('mode', 'host')->json('heading');

            $this->assertMatchesRegularExpression('/>\s*' . preg_quote($ip, '/') . '\s*<\/h2>/', $heading);
            $this->assertStringNotContainsString('nameUrl:', $heading);
        }
    }

    public function testOnlyAddressesWorthANameAreLookedUp(): void
    {
        [$router, $port] = $this->portOn();
        foreach (['169.254.10.6', '198.51.100.41'] as $ip) {
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => '02f2f2f2f004', 'ipv4_address' => $ip]);
        }

        $heading = urldecode($this->results(['q' => '02:f2:f2:f2:f0:04'])->assertJsonPath('mode', 'host')->json('heading'));

        $this->assertStringContainsString('198.51.100.41', $heading);
        $this->assertStringNotContainsString('ips[0]=169.254.10.6', $heading);
        $this->assertStringNotContainsString('ips[1]=169.254.10.6', $heading);
    }

    public function testHostNameStopsAtTheFirstName(): void
    {
        $this->mock(Dns::class, function ($dns): void {
            $dns->shouldReceive('reverseHostname')->once()->with('198.51.100.32')->andReturn(null);
            $dns->shouldReceive('reverseHostname')->once()->with('198.51.100.33')->andReturn('found.example.test');
            $dns->shouldReceive('getAddresses')->once()->with('found.example.test')->andReturn(['198.51.100.99', '198.51.100.33']);
            $dns->shouldNotReceive('reverseHostname')->with('198.51.100.34');
        });

        $url = LaravelUrl::signedRoute('search.name', ['ips' => ['198.51.100.32', '198.51.100.33', '198.51.100.34']]);

        $this->actingAs($this->admin())->getJson($url)->assertOk()->assertExactJson(['name' => 'found.example.test', 'ip' => '198.51.100.33', 'forward_match' => true]);
    }

    public function testReverseDnsMismatchIsStillShown(): void
    {
        $this->mock(Dns::class, function ($dns): void {
            $dns->shouldReceive('reverseHostname')->once()->with('198.51.100.37')->andReturn('stale.example.test');
            $dns->shouldReceive('getAddresses')->once()->with('stale.example.test')->andReturn(['198.51.100.38']);
        });

        $url = LaravelUrl::signedRoute('search.name', ['ips' => ['198.51.100.37']]);

        $this->actingAs($this->admin())->getJson($url)->assertOk()->assertExactJson([
            'name' => 'stale.example.test', 'ip' => '198.51.100.37', 'forward_match' => false,
        ]);
    }

    public function testReverseDnsWithoutForwardAddressesIsAMismatch(): void
    {
        $this->mock(Dns::class, function ($dns): void {
            $dns->shouldReceive('reverseHostname')->andReturn('missing.example.test');
            $dns->shouldReceive('getAddresses')->andReturn([]);
        });

        $url = LaravelUrl::signedRoute('search.name', ['ips' => ['198.51.100.39']]);

        $this->actingAs($this->admin())->getJson($url)->assertOk()->assertJsonPath('forward_match', false);
    }

    public function testReverseDnsMatchesEquivalentIpv6Addresses(): void
    {
        $this->mock(Dns::class, function ($dns): void {
            $dns->shouldReceive('reverseHostname')->with('2001:db8::1')->andReturn('ipv6.example.test');
            $dns->shouldReceive('getAddresses')->with('ipv6.example.test')->andReturn(['2001:0db8:0:0:0:0:0:1']);
        });

        $url = LaravelUrl::signedRoute('search.name', ['ips' => ['2001:db8::1']]);

        $this->actingAs($this->admin())->getJson($url)->assertOk()->assertJsonPath('forward_match', true);
    }

    public function testHostNameIsNullWhenNothingResolves(): void
    {
        $this->mock(Dns::class, fn ($dns) => $dns->shouldReceive('reverseHostname')->andReturn(null));

        $url = LaravelUrl::signedRoute('search.name', ['ips' => ['198.51.100.35']]);

        $this->actingAs($this->admin())->getJson($url)->assertOk()->assertExactJson(['name' => null, 'ip' => null, 'forward_match' => null]);
    }

    public function testHostNameOnlyLooksUpAddressesTheSearchGave(): void
    {
        $this->mock(Dns::class, fn ($dns) => $dns->shouldNotReceive('reverseHostname'));

        $this->actingAs($this->admin())->getJson(route('search.name', ['ips' => ['198.51.100.36']]))->assertForbidden();
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

        $host = new HostLookup($this->admin())->find([new HostKey([IP::parse('10.70.200.1')])], follow: true)[0];

        $this->assertFalse($host->isNotSeen());
        $this->assertContains('10.70.200.1', array_map(fn (NeighborEntry $entry): string => $entry->ip->compressed(), $host->entries));
    }
}
