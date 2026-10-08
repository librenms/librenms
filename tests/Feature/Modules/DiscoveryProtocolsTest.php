<?php

/**
 * DiscoveryProtocolsTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\Modules;

use App\Actions\Device\AutoDiscoverDevice;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Modules\DiscoveryProtocols;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Mockery\MockInterface;

final class DiscoveryProtocolsTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;
    private Port $localPort;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('autodiscovery.xdp', false);
        LibrenmsConfig::set('autodiscovery.ospf', false);
        LibrenmsConfig::set('autodiscovery.ospfv3', false);
        LibrenmsConfig::set('discovery_by_ip', false);
        LibrenmsConfig::set('autodiscovery.xdp_exclude.sysname_regexp', []);
        LibrenmsConfig::set('autodiscovery.xdp_exclude.sysdesc_regexp', []);
        LibrenmsConfig::set('autodiscovery.cdp_exclude.platform_regexp', []);

        $this->device = Device::factory()->create(['hostname' => 'local.dp.test', 'ip' => null, 'os' => 'generic']);
        $this->localPort = Port::factory()->create(['device_id' => $this->device->device_id, 'ifName' => 'Gi0/1', 'ifIndex' => 1]);
    }

    public function testLinksAreSynced(): void
    {
        $module = new DiscoveryProtocols;

        $module->discover($this->os(
            lldp: [
                $this->neighbor(['sysName' => 'switch-a', 'portId' => 'eth1', 'sysDescr' => 'Switch A']),
                $this->neighbor(['sysName' => 'switch-b', 'portId' => 'eth2']),
            ],
            cdp: [
                $this->neighbor(['protocol' => 'cdp', 'sysName' => 'router-c', 'portId' => 'Gi0/0', 'platform' => 'cisco ISR4331']),
            ],
        ));

        $links = $this->device->links()->orderBy('remote_hostname')->get();
        $this->assertSame(['router-c', 'switch-a', 'switch-b'], $links->pluck('remote_hostname')->all());
        $this->assertSame(['cdp', 'lldp', 'lldp'], $links->pluck('protocol')->all());
        $this->assertSame(['cisco ISR4331', null, null], $links->pluck('remote_platform')->all());
        $this->assertSame('Switch A', $links[1]->remote_version);
        $this->assertEquals([$this->localPort->port_id], $links->pluck('local_port_id')->unique()->values()->all());
        $this->assertEquals([0], $links->pluck('remote_device_id')->unique()->values()->all());
        $this->assertTrue($module->dataExists($this->device));

        $kept_id = $links[1]->id;
        $module->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'switch-a', 'portId' => 'eth1', 'sysDescr' => 'Switch A v2']),
        ]));

        $link = $this->device->links()->sole();
        $this->assertSame($kept_id, $link->id, 'Existing link should be updated, not replaced');
        $this->assertSame('Switch A v2', $link->remote_version);

        $this->assertSame(1, $module->cleanup($this->device));
        $this->assertFalse($module->dataExists($this->device));
    }

    public function testLinksToKnownDevicesAndPorts(): void
    {
        $remote = Device::factory()->create(['hostname' => 'remote.dp.test', 'sysName' => 'remote', 'ip' => null, 'os' => 'generic']);
        $remotePort = Port::factory()->create(['device_id' => $remote->device_id, 'ifName' => 'xe-0/0/1', 'ifIndex' => 501]);

        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'remote.dp.test', 'portId' => 'xe-0/0/1']),
            // no name advertised, found by mac, named after the device
            $this->neighbor(['chassisMac' => 'feed00aa0001', 'portId' => 'xe-0/0/1']),
        ]));
        Port::where('port_id', $remotePort->port_id)->update(['ifPhysAddress' => 'feed00aa0001']);
        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'remote.dp.test', 'portId' => 'xe-0/0/1']),
            $this->neighbor(['chassisMac' => 'feed00aa0001', 'portId' => 'xe-0/0/1']),
        ]));

        $links = $this->device->links()->orderBy('remote_hostname')->get();
        $this->assertSame(['remote', 'remote.dp.test'], $links->pluck('remote_hostname')->all());
        $this->assertEquals([$remote->device_id, $remote->device_id], $links->pluck('remote_device_id')->all());
        $this->assertEquals([$remotePort->port_id, $remotePort->port_id], $links->pluck('remote_port_id')->all());
    }

    public function testNeighborsWithoutLocalPortOrNameAreSkipped(): void
    {
        (new DiscoveryProtocols)->discover($this->os(lldp: [
            new Neighbor('lldp', null, sysName: 'no-local-port', portId: 'eth0'),
            $this->neighbor(['portId' => 'eth0']), // no name
            $this->neighbor(['sysDescr' => 'HPE ProLiant DL360 Gen10']), // only a description
        ]));

        $link = $this->device->links()->sole();
        $this->assertSame('HPE ProLiant DL360 Gen10', $link->remote_hostname);
        $this->assertSame('', $link->remote_port);
    }

    public function testRemotePortLabel(): void
    {
        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'by-name', 'portId' => 'eth0', 'portDescr' => 'uplink']),
            $this->neighbor(['sysName' => 'by-mac', 'portId' => 'aca31ec34be6', 'portIdSubtype' => LldpPortIdSubtype::MacAddress, 'portMac' => 'aca31ec34be6']),
            $this->neighbor(['sysName' => 'by-descr', 'portDescr' => 'PCI-E Slot 3, Port 1']),
            $this->neighbor(['sysName' => 'vmware', 'portId' => 'vmnic5', 'portMac' => '000af7ecd161']),
        ]));

        $this->assertSame(
            ['by-descr' => 'PCI-E Slot 3, Port 1', 'by-mac' => 'ac:a3:1e:c3:4b:e6', 'by-name' => 'eth0', 'vmware' => 'vmnic5'],
            $this->device->links()->orderBy('remote_hostname')->pluck('remote_port', 'remote_hostname')->all(),
        );
    }

    public function testAutodiscovery(): void
    {
        LibrenmsConfig::set('autodiscovery.xdp', true);
        LibrenmsConfig::set('discovery_by_ip', true);
        $added = Device::factory()->create(['hostname' => 'added.dp.test', 'ip' => null, 'os' => 'generic']);

        $this->mockAutoDiscover(function (MockInterface $mock) use ($added): void {
            // each target is only attempted once, even if seen on several ports
            $mock->shouldReceive('execute')->once()->with('unresolvable', Mockery::any(), 'LLDP', Mockery::type(Port::class))->andReturnNull();
            $mock->shouldReceive('execute')->once()->with('192.0.2.10', Mockery::any(), 'LLDP', Mockery::type(Port::class))->andReturn($added);
            $mock->shouldReceive('execute')->once()->with('new-cdp', Mockery::any(), 'CDP', Mockery::type(Port::class))->andReturnNull();
        });

        (new DiscoveryProtocols)->discover($this->os(
            lldp: [
                $this->neighbor(['sysName' => 'unresolvable', 'managementIp' => '192.0.2.10', 'portId' => 'eth0']),
                $this->neighbor(['sysName' => 'unresolvable', 'managementIp' => '192.0.2.10', 'portId' => 'eth1']),
            ],
            cdp: [
                $this->neighbor(['protocol' => 'cdp', 'sysName' => 'new-cdp', 'portId' => 'Gi0/0']),
            ],
        ));

        $this->assertEquals(
            [$added->device_id, $added->device_id, 0],
            $this->device->links()->orderBy('protocol', 'desc')->orderBy('remote_port')->pluck('remote_device_id')->all(),
        );
    }

    public function testAutodiscoveryTargetsAreNormalized(): void
    {
        LibrenmsConfig::set('autodiscovery.xdp', true);
        LibrenmsConfig::set('discovery_by_ip', true);

        $this->mockAutoDiscover(function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->with('New-Switch', Mockery::any(), 'LLDP', Mockery::any())->andReturnNull();
            $mock->shouldReceive('execute')->once()->with('2001:db8::10', Mockery::any(), 'LLDP', Mockery::any())->andReturnNull();
        });

        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'New-Switch', 'managementIp' => '2001:db8::10', 'portId' => 'eth0']),
            $this->neighbor(['sysName' => 'new-switch.', 'managementIp' => '2001:0db8:0000:0000:0000:0000:0000:0010', 'portId' => 'eth1']),
        ]));

        $this->assertSame(2, $this->device->links()->count());
    }

    public function testAutodiscoveryExclusions(): void
    {
        LibrenmsConfig::set('autodiscovery.xdp', true);
        LibrenmsConfig::set('autodiscovery.xdp_exclude.sysname_regexp', ['/^phone-/']);
        LibrenmsConfig::set('autodiscovery.xdp_exclude.sysdesc_regexp', ['/linux/']);
        LibrenmsConfig::set('autodiscovery.cdp_exclude.platform_regexp', ['/^cisco ip phone/']);

        $this->mockAutoDiscover(function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->with('allowed', Mockery::any(), 'LLDP', Mockery::any())->andReturnNull();
        });

        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'PHONE-1234', 'portId' => 'eth0']),
            $this->neighbor(['sysName' => 'server1', 'sysDescr' => 'Debian GNU/Linux', 'portId' => 'eth0']),
            $this->neighbor(['sysName' => 'SEP0011', 'platform' => 'Cisco IP Phone 8841', 'portId' => 'eth0']),
            $this->neighbor(['sysName' => 'allowed', 'portId' => 'eth0']),
        ]));

        $this->assertSame(4, $this->device->links()->count());
    }

    public function testNoAutodiscoveryWhenDisabled(): void
    {
        $this->mockAutoDiscover(fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));

        (new DiscoveryProtocols)->discover($this->os(lldp: [
            $this->neighbor(['sysName' => 'unknown', 'managementIp' => '192.0.2.10', 'portId' => 'eth0']),
        ]));

        $this->assertSame(1, $this->device->links()->count());
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function neighbor(array $args): Neighbor
    {
        return new Neighbor(...['protocol' => 'lldp', 'localPortId' => $this->localPort->port_id, ...$args]);
    }

    /**
     * @param  Neighbor[]  $lldp
     * @param  Neighbor[]  $cdp
     */
    private function os(array $lldp = [], array $cdp = []): OS
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('discoverNeighbors')->andReturn(new Collection([...$cdp, ...$lldp]));

        /** @var OS $os */
        return $os;
    }

    private function mockAutoDiscover(callable $expectations): void
    {
        $this->app->instance(AutoDiscoverDevice::class, Mockery::mock(AutoDiscoverDevice::class, $expectations));
    }
}
