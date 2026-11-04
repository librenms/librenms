<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Http\Controllers\Search\Data\Gateway;
use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\HostSubnet;
use App\Http\Controllers\Search\Data\SubnetAddress;
use App\Http\Controllers\Search\HostLookup;
use App\Http\Controllers\Search\SubnetLookup;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\Route;
use Illuminate\Support\Collection;
use LibreNMS\Util\IP;

class SubnetLookupTest extends SearchTestCase
{
    private function subnetOf(string $ip): HostSubnet
    {
        $admin = $this->admin();
        $host = (new HostLookup($admin))->find([new HostKey([IP::parse($ip)])])[0];

        return (new SubnetLookup($admin))->find($host);
    }

    /**
     * @param  Collection<int, covariant Gateway|SubnetAddress>  $found
     * @return list<string>
     */
    private function ips(Collection $found): array
    {
        return $found->map(fn (Gateway|SubnetAddress $item): string => $item->ip->compressed())->values()->all();
    }

    public function testSameSubnetIsTheNeighborInterfaceDespiteWrongPrefixLengths(): void
    {
        [$router, $vlan411] = $this->portOn('vlan-411');
        [, $vlan603] = $this->portOn('vlan-603');
        Ipv6Nd::factory()->create(['device_id' => $router->device_id, 'port_id' => $vlan411->port_id, 'mac_address' => '02e0e0e0e001', 'ipv6_address' => '2001:0db8:0001:0002:0000:0000:0000:0070']);
        // Comware reports an index here, these lengths would make both addresses seem to contain the host
        Ipv6Address::factory()->create(['port_id' => $vlan411->port_id, 'ipv6_address' => '2001:0db8:0000:0002:0000:0000:0000:000f', 'ipv6_compressed' => '2001:db8:0:2::f', 'ipv6_prefixlen' => 16]);
        Ipv6Address::factory()->create(['port_id' => $vlan411->port_id, 'ipv6_address' => '2001:0db8:0001:0002:0000:0000:0000:000f', 'ipv6_compressed' => '2001:db8:1:2::f', 'ipv6_prefixlen' => 17]);
        Ipv6Address::factory()->create(['port_id' => $vlan411->port_id, 'ipv6_address' => 'fe80:0000:0000:0000:0000:0000:0000:000f', 'ipv6_compressed' => 'fe80::f', 'ipv6_prefixlen' => 18]);
        Ipv6Address::factory()->create(['port_id' => $vlan603->port_id, 'ipv6_address' => '2001:0db8:0000:000e:0000:0000:0000:0009', 'ipv6_compressed' => '2001:db8:0:e::9', 'ipv6_prefixlen' => 19]);

        $subnet = $this->subnetOf('2001:db8:1:2::70');
        $addresses = $subnet->peers;

        $this->assertCount(1, $addresses);
        $this->assertSame('2001:db8:1:2::f', $addresses[0]->ip->compressed());
    }

    public function testSameSubnetFallsBackToTheMostSpecificSubnet(): void
    {
        [, $wide] = $this->portOn();
        [, $narrow] = $this->portOn();
        [, $bogus] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $wide->port_id, 'ipv4_address' => '10.77.0.1', 'ipv4_prefixlen' => 16]);
        Ipv4Address::factory()->create(['port_id' => $narrow->port_id, 'ipv4_address' => '10.77.1.1', 'ipv4_prefixlen' => 24]);
        Ipv6Address::factory()->create(['port_id' => $narrow->port_id, 'ipv6_address' => '2001:0db8:0077:0001:0000:0000:0000:0001', 'ipv6_compressed' => '2001:db8:77:1::1', 'ipv6_prefixlen' => 64]);
        Ipv6Address::factory()->create(['port_id' => $bogus->port_id, 'ipv6_address' => '2001:0db8:0077:0002:0000:0000:0000:0001', 'ipv6_compressed' => '2001:db8:77:2::1', 'ipv6_prefixlen' => 19]);

        $ipv4 = $this->subnetOf('10.77.1.50')->peers;
        $this->assertSame(['10.77.1.1'], $this->ips($ipv4));

        $ipv6 = $this->subnetOf('2001:db8:77:1::50')->peers;
        $this->assertSame(['2001:db8:77:1::1'], $this->ips($ipv6));
    }

    private function defaultRoute(Port $port, string $nextHop, string $dest = '0.0.0.0'): void
    {
        Route::factory()->create([
            'device_id' => $port->device_id,
            'port_id' => $port->port_id,
            'context_name' => '',
            'inetCidrRouteDest' => $dest,
            'inetCidrRoutePfxLen' => 0,
            'inetCidrRouteNextHop' => $nextHop,
        ]);
    }

    public function testGatewayIsTheNextHopOfDefaultRoutesOnTheSubnet(): void
    {
        [, $first] = $this->portOn();
        [, $second] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $first->port_id, 'ipv4_address' => '10.79.1.2', 'ipv4_prefixlen' => 24]);
        Ipv4Address::factory()->create(['port_id' => $second->port_id, 'ipv4_address' => '10.79.1.3', 'ipv4_prefixlen' => 24]);
        $this->defaultRoute($first, '10.79.1.1');
        $this->defaultRoute($second, '10.79.1.1');
        // not a default route, and a default route leaving through another network
        Route::factory()->create(['device_id' => $first->device_id, 'port_id' => $first->port_id, 'context_name' => '', 'inetCidrRouteDest' => '10.0.0.0', 'inetCidrRoutePfxLen' => 8, 'inetCidrRouteNextHop' => '10.79.1.9']);
        $this->defaultRoute($second, '192.0.2.1');

        $subnet = $this->subnetOf('10.79.1.50');
        $gateways = $subnet->gateways;

        $this->assertCount(1, $gateways);
        $this->assertSame('10.79.1.1', $gateways[0]->ip->compressed());
        $this->assertSame(2, $gateways[0]->devices);
        $this->assertNull($gateways[0]->port);

        $html = $this->results(['q' => '10.79.1.50'])->json('html');
        $this->assertStringContainsString(__('search.host.gateway'), $html);
        $this->assertStringContainsString('10.79.1.1', $html);
    }

    public function testMonitoredGatewayShowsItsInterfaceOnce(): void
    {
        [, $switch] = $this->portOn('switch-vlan');
        [, $router] = $this->portOn('router-vlan');
        Ipv4Address::factory()->create(['port_id' => $switch->port_id, 'ipv4_address' => '10.80.1.2', 'ipv4_prefixlen' => 24]);
        Ipv4Address::factory()->create(['port_id' => $router->port_id, 'ipv4_address' => '10.80.1.1', 'ipv4_prefixlen' => 24]);
        $this->defaultRoute($switch, '10.80.1.1');
        // the router's own default route leaves this subnet
        $this->defaultRoute($router, '192.0.2.1');

        $subnet = $this->subnetOf('10.80.1.50');
        $gateways = $subnet->gateways;

        $this->assertSame(['10.80.1.1'], $this->ips($gateways));
        $this->assertSame($router->port_id, $gateways[0]->port?->port_id);
        $this->assertSame(['10.80.1.2'], $this->ips($subnet->peers));
    }

    public function testIpv6GatewayAcceptsALinkLocalNextHopOnTheSamePort(): void
    {
        [, $onLink] = $this->portOn();
        [, $otherLink] = $this->portOn();
        Ipv6Address::factory()->create(['port_id' => $onLink->port_id, 'ipv6_address' => '2001:0db8:0081:0001:0000:0000:0000:0002', 'ipv6_compressed' => '2001:db8:81:1::2', 'ipv6_prefixlen' => 64]);
        Ipv6Address::factory()->create(['port_id' => $otherLink->port_id, 'ipv6_address' => '2001:0db8:0081:0001:0000:0000:0000:0003', 'ipv6_compressed' => '2001:db8:81:1::3', 'ipv6_prefixlen' => 64]);
        $unspecified = '0000:0000:0000:0000:0000:0000:0000:0000';
        $this->defaultRoute($onLink, 'fe80:0000:0000:0000:0000:0000:0000:0001', $unspecified);
        // leaves through an interface that isn't on the subnet
        $port = Port::factory()->for($otherLink->device)->create();
        $this->defaultRoute($port, 'fe80:0000:0000:0000:0000:0000:0000:0002', $unspecified);

        $subnet = $this->subnetOf('2001:db8:81:1::50');
        $gateways = $subnet->gateways;

        $this->assertSame(['fe80::1'], $this->ips($gateways));
        $this->assertNull($gateways[0]->port);
    }

    public function testGatewayComesFromAnyDeviceOnTheSubnetNotJustThoseThatSawTheHost(): void
    {
        [$neighbor, $neighborPort] = $this->portOn();
        [, $bystander] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $neighborPort->port_id, 'ipv4_address' => '10.83.1.2', 'ipv4_prefixlen' => 24]);
        Ipv4Address::factory()->create(['port_id' => $bystander->port_id, 'ipv4_address' => '10.83.1.3', 'ipv4_prefixlen' => 24]);
        Ipv4Mac::factory()->create(['device_id' => $neighbor->device_id, 'port_id' => $neighborPort->port_id, 'ipv4_address' => '10.83.1.50', 'mac_address' => '02e0e0e0e083']);
        $this->defaultRoute($bystander, '10.83.1.1');

        $html = $this->results(['q' => '10.83.1.50'])->json('html');

        $this->assertStringContainsString(__('search.host.gateway'), $html);
        $this->assertStringContainsString('10.83.1.1', $html);
    }

    private function route(Port $port, string $dest, int $prefix, string $nextHop): void
    {
        Route::factory()->create([
            'device_id' => $port->device_id,
            'port_id' => $port->port_id,
            'context_name' => '',
            'inetCidrRouteDest' => $dest,
            'inetCidrRoutePfxLen' => $prefix,
            'inetCidrRouteNextHop' => $nextHop,
        ]);
    }

    /**
     * A device of this type on the subnet of 10.84.1.50
     */
    private function portBeside(string $deviceType): Port
    {
        [, $port] = $this->portOn(device: ['type' => $deviceType]);
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '10.84.1.254', 'ipv4_prefixlen' => 24]);

        return $port;
    }

    public function testRouterWithTheSubnetConnectedIsTheGateway(): void
    {
        $port = $this->portBeside('network');
        $this->route($port, '10.84.1.0', 24, '0.0.0.0');
        $this->defaultRoute($port, '192.0.2.1');

        $gateways = $this->subnetOf('10.84.1.50')->gateways;

        $this->assertSame(['10.84.1.254'], $this->ips($gateways));
        $this->assertSame($port->port_id, $gateways[0]->port?->port_id);
        $this->assertSame(0, $gateways[0]->devices);

        $html = $this->results(['q' => '10.84.1.50'])->json('html');
        $this->assertStringContainsString(__('search.host.gateway'), $html);
        $this->assertStringContainsString(e(__('search.host.gateway_router_help')), $html);
    }

    public function testRouterConnectedRouteCanUseItsOwnAddressAsTheNextHop(): void
    {
        $port = $this->portBeside('firewall');
        $this->route($port, '10.84.1.0', 24, '10.84.1.254');
        $this->route($port, '10.99.0.0', 16, '192.0.2.1');

        $this->assertCount(1, $this->subnetOf('10.84.1.50')->gateways);
    }

    public function testConnectedRouteAloneIsNotARouter(): void
    {
        $port = $this->portBeside('network');
        $this->route($port, '10.84.1.0', 24, '0.0.0.0');

        $this->assertCount(0, $this->subnetOf('10.84.1.50')->gateways);
    }

    public function testServerWithTheSubnetConnectedIsNotAGateway(): void
    {
        $port = $this->portBeside('server');
        $this->route($port, '10.84.1.0', 24, '0.0.0.0');
        $this->defaultRoute($port, '192.0.2.1');

        $this->assertCount(0, $this->subnetOf('10.84.1.50')->gateways);
    }

    public function testRoutesOffTheSubnetWithoutItConnectedIsNotAGateway(): void
    {
        $port = $this->portBeside('network');
        $this->route($port, '10.84.0.0', 16, '192.0.2.1');
        $this->defaultRoute($port, '192.0.2.1');

        $this->assertCount(0, $this->subnetOf('10.84.1.50')->gateways);
    }

    public function testDefaultRouteNextHopWinsOverConnectedRouters(): void
    {
        $port = $this->portBeside('network');
        $this->route($port, '10.84.1.0', 24, '0.0.0.0');
        $this->defaultRoute($port, '10.84.1.1');

        $this->assertSame(['10.84.1.1'], $this->ips($this->subnetOf('10.84.1.50')->gateways));
    }

    public function testMonitoredHostShowsItsGateway(): void
    {
        [$router, $routerPort] = $this->portOn('router-link', ['type' => 'network']);
        Ipv4Address::factory()->create(['port_id' => $routerPort->port_id, 'ipv4_address' => '10.82.5.254', 'ipv4_prefixlen' => 24]);
        $this->route($routerPort, '10.82.5.0', 24, '0.0.0.0');
        $this->defaultRoute($routerPort, '192.0.2.1');

        // only known by its management IP and the MAC of one of its ports, like a newly added device
        $device = Device::factory()->create(['ip' => inet_pton('10.82.5.7')]);
        Port::factory()->for($device)->create(['ifPhysAddress' => '02c8c8c8c001']);
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02c8c8c8c001', 'ipv4_address' => '10.82.5.7']);

        $html = $this->results(['q' => '10.82.5.7'])->assertJsonPath('mode', 'host')->json('html');

        $this->assertStringContainsString(e(__('search.host.gateway_router_help')), $html);
        $this->assertStringContainsString('10.82.5.254', $html);
    }

    public function testMonitoredHostsOwnDefaultRouteNamesItsGateway(): void
    {
        [, $port] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '10.81.5.7', 'ipv4_prefixlen' => 24]);
        $this->defaultRoute($port, '10.81.5.1');

        $subnet = $this->subnetOf('10.81.5.7');

        $this->assertSame(['10.81.5.1'], $this->ips($subnet->gateways));
    }

    public function testRoutersOwnUpstreamIsNotTheGatewayOfItsOtherSubnets(): void
    {
        [, $lan] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $lan->port_id, 'ipv4_address' => '10.80.5.254', 'ipv4_prefixlen' => 24]);
        $this->route($lan, '10.80.5.0', 24, '0.0.0.0');
        $this->defaultRoute($lan, '192.0.2.1');

        $subnet = $this->subnetOf('10.80.5.254');

        $this->assertCount(0, $subnet->gateways);
    }

    public function testHostIsNotItsOwnGateway(): void
    {
        [, $port] = $this->portOn();
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '10.82.1.2', 'ipv4_prefixlen' => 24]);
        $this->defaultRoute($port, '10.82.1.1');

        $subnet = $this->subnetOf('10.82.1.1');

        $this->assertCount(0, $subnet->gateways);
    }

    public function testSameSubnetListsNetworkDevicesFirstWithoutCallingThemAGateway(): void
    {
        $server = Device::factory()->create(['type' => 'server']);
        $serverPort = Port::factory()->for($server)->create();
        $switch = Device::factory()->create(['type' => 'network']);
        $switchPort = Port::factory()->for($switch)->create();
        Ipv4Address::factory()->create(['port_id' => $serverPort->port_id, 'ipv4_address' => '10.78.1.2', 'ipv4_prefixlen' => 24]);
        Ipv4Address::factory()->create(['port_id' => $switchPort->port_id, 'ipv4_address' => '10.78.1.3', 'ipv4_prefixlen' => 24]);

        $subnet = $this->subnetOf('10.78.1.50');

        $this->assertSame(['10.78.1.3', '10.78.1.2'], $this->ips($subnet->peers));

        $html = $this->results(['q' => '10.78.1.50'])->json('html');
        $this->assertStringContainsString(__('search.host.same_subnet'), $html);
        $this->assertStringContainsString(__('search.not_found.title'), $html);
        $this->assertStringNotContainsString('Gateway', $html);
    }
}
