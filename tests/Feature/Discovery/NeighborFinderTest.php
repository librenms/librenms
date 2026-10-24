<?php

/**
 * NeighborFinderTest.php
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

namespace LibreNMS\Tests\Feature\Discovery;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborFinder;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Every case is resolved against the devices in NETWORK.
 *
 * To add a case, add an entry to neighborProvider():
 *   'neighbor' => named arguments for Neighbor (protocol and localPortId are filled in)
 *   'device'   => expected device hostname, or null if no device should be found
 *   'port'     => expected port ifName on that device, or null if no port should be found
 *   'config'   => (optional) LibreNMS config settings for this case
 * If the case needs a device or port that does not exist yet, add it to NETWORK.
 */
final class NeighborFinderTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * hostname => device attributes, ports are keyed by ifName.
     * Set 'hostname' to override the key (for duplicate hostnames).
     * Port attributes default to ifDescr = ifName, other port attributes are null unless set.
     * Port 'ipv4' assigns that address to the port.
     * MACs start with feed to avoid colliding with other test data.
     */
    private const NETWORK = [
        'core1.neighbor.test' => [
            'sysName' => 'core1',
            'ip' => '192.0.2.1',
            'ports' => [
                'Gi1/0/1' => ['ifIndex' => 1, 'ifDescr' => 'GigabitEthernet1/0/1', 'ifAlias' => 'uplink to dist1'],
                'Gi1/0/2' => ['ifIndex' => 2, 'ifDescr' => 'GigabitEthernet1/0/2'],
                'Gi1/0/3' => ['ifIndex' => 3, 'ifDescr' => 'GigabitEthernet1/0/3', 'ifAlias' => 'server rack 3'],
                'Gi1/0/4' => ['ifIndex' => 4, 'ifDescr' => 'GigabitEthernet1/0/4'],
                'Gi1/0/5' => ['ifIndex' => 5, 'ifDescr' => 'GigabitEthernet1/0/5', 'ifPhysAddress' => 'feed00010105'],
                'Gi1/0/9' => ['ifIndex' => 9, 'ifDescr' => 'GigabitEthernet1/0/9', 'deleted' => 1],
                'Vl10' => ['ifIndex' => 10010, 'ifDescr' => 'Vlan10', 'ipv4' => '198.51.100.1'],
            ],
        ],
        'dist1.neighbor.test' => [
            'sysName' => 'DIST-1',
            'ip' => '2001:db8::2',
            'ports' => [
                'xe-0/0/0' => ['ifIndex' => 500, 'ipv4' => '198.51.100.2'],
                'xe-0/0/1' => ['ifIndex' => 501, 'ifAlias' => 'core1 Gi1/0/1'],
                'lo0' => ['ifIndex' => 16, 'ipv4' => '203.0.113.2'],
            ],
        ],
        'edge1' => [
            'sysName' => 'Edge Router',
            'ports' => ['ge-0/0/0' => ['ifIndex' => 510]],
        ],
        '192.0.2.50' => [
            'sysName' => 'ap1',
            'ports' => ['eth0' => ['ifIndex' => 2, 'ifPhysAddress' => 'feed00050002']],
        ],
        '192.0.2.60' => [
            'sysName' => 'core-sw.neighbor.test',
            'ports' => ['1/1/1' => ['ifIndex' => 1001]],
        ],
        'shared-name.neighbor.test' => [
            'sysName' => 'shared-a',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        '192.0.2.61' => [
            'sysName' => 'shared-name.neighbor.test',
            'ports' => ['eth1' => ['ifIndex' => 1]],
        ],
        'dupe-a.neighbor.test' => [
            'sysName' => 'dupe',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        'dupe-b.neighbor.test' => [
            'sysName' => 'dupe',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        'rtr-a.neighbor.test' => [
            'sysName' => 'rtr-a',
            'ports' => ['vrrp1' => ['ifIndex' => 1, 'ifPhysAddress' => 'feed5e000101', 'ipv4' => '198.51.100.254']],
        ],
        'rtr-b.neighbor.test' => [
            'sysName' => 'rtr-b',
            'ports' => ['vrrp1' => ['ifIndex' => 1, 'ifPhysAddress' => 'feed5e000101', 'ipv4' => '198.51.100.254']],
        ],
        'linksys.neighbor.test' => [
            'sysName' => 'linksys',
            'ports' => [
                'g1' => ['ifIndex' => 1, 'ifDescr' => 'Ethernet Interface', 'ifPhysAddress' => 'feed0000aaaa'],
                'g2' => ['ifIndex' => 2, 'ifDescr' => 'Ethernet Interface', 'ifPhysAddress' => 'feed0000aaaa'],
                'g3' => ['ifIndex' => 3, 'ifDescr' => 'Ethernet Interface', 'ifPhysAddress' => 'feed0000aaaa'],
            ],
        ],
        'twin-a' => [
            'hostname' => 'twin.neighbor.test',
            'sysName' => 'twin-a',
            'ip' => '192.0.2.70',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        'twin-b' => [
            'hostname' => 'twin.neighbor.test',
            'sysName' => 'twin-b',
            'ip' => '192.0.2.71',
            'ports' => ['eth1' => ['ifIndex' => 1]],
        ],
        'edge2' => [
            'sysName' => 'edge2-short',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        'edge2.neighbor.test' => [
            'sysName' => 'edge2-fqdn',
            'ports' => ['eth0' => ['ifIndex' => 1]],
        ],
        'esx1.neighbor.test' => [
            'sysName' => 'esx1',
            'ports' => [
                'vmnic0' => ['ifIndex' => 1, 'ifPhysAddress' => 'feed000e0000'],
                'vmnic1' => ['ifIndex' => 2, 'ifPhysAddress' => 'feed000e0001'],
            ],
        ],
        'xos1.neighbor.test' => [
            'os' => 'xos',
            'sysName' => 'xos1',
            'ports' => [
                'Mgmt' => ['ifIndex' => 5],
                'Slot-1 Port 5' => ['ifIndex' => 1005],
                'Slot-2 Port 5' => ['ifIndex' => 2005],
            ],
        ],
        'calix1.neighbor.test' => [
            'os' => 'calix',
            'sysName' => 'calix1',
            'ports' => [
                'EthPort 3' => ['ifIndex' => 103],
            ],
        ],
        'gs108t.neighbor.test' => [
            'os' => 'netgear',
            'sysName' => 'gs108t',
            'sysDescr' => 'GS108T',
            'ports' => [
                'Port 1 Gigabit Ethernet' => ['ifIndex' => 1],
                'Port 2 Gigabit Ethernet' => ['ifIndex' => 2],
            ],
        ],
    ];

    /**
     * @return array<string, array{neighbor: array<string, mixed>, device: ?string, port: ?string, config?: array<string, mixed>}>
     */
    public static function neighborProvider(): array
    {
        return [
            // ---- device by hostname ----
            'sysName matches hostname' => [
                'neighbor' => ['sysName' => 'core1.neighbor.test', 'portId' => 'Gi1/0/1'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/1',
            ],
            'short name matches hostname with mydomain' => [
                'neighbor' => ['sysName' => 'dist1', 'portId' => 'xe-0/0/0'],
                'device' => 'dist1.neighbor.test',
                'port' => 'xe-0/0/0',
                'config' => ['mydomain' => 'neighbor.test'],
            ],
            'short name does not match hostname without mydomain' => [
                'neighbor' => ['sysName' => 'dist1', 'portId' => 'xe-0/0/0'],
                'device' => null,
                'port' => null,
            ],
            'fqdn matches short hostname with mydomain' => [
                'neighbor' => ['sysName' => 'edge1.neighbor.test', 'portId' => 'ge-0/0/0'],
                'device' => 'edge1',
                'port' => 'ge-0/0/0',
                'config' => ['mydomain' => 'neighbor.test'],
            ],
            'exact hostname is preferred over hostname with mydomain' => [
                'neighbor' => ['sysName' => 'edge2', 'portId' => 'eth0'],
                'device' => 'edge2',
                'port' => 'eth0',
                'config' => ['mydomain' => 'neighbor.test'],
            ],
            'exact fqdn hostname is preferred over short hostname with mydomain' => [
                'neighbor' => ['sysName' => 'edge2.neighbor.test', 'portId' => 'eth0'],
                'device' => 'edge2.neighbor.test',
                'port' => 'eth0',
                'config' => ['mydomain' => 'neighbor.test'],
            ],
            'duplicate hostname is ambiguous' => [
                'neighbor' => ['sysName' => 'twin.neighbor.test', 'portId' => 'eth0'],
                'device' => null,
                'port' => null,
            ],
            'duplicate hostname falls back to management ip' => [
                'neighbor' => ['sysName' => 'twin.neighbor.test', 'managementIp' => '192.0.2.71', 'portId' => 'eth1'],
                'device' => 'twin.neighbor.test',
                'port' => 'eth1',
            ],
            'hostname is preferred over sysName' => [
                'neighbor' => ['sysName' => 'shared-name.neighbor.test', 'portId' => 'eth0'],
                'device' => 'shared-name.neighbor.test',
                'port' => 'eth0',
            ],

            // ---- device by sysName ----
            'sysName matches sysName' => [
                'neighbor' => ['sysName' => 'DIST-1', 'portId' => 'xe-0/0/1'],
                'device' => 'dist1.neighbor.test',
                'port' => 'xe-0/0/1',
            ],
            'sysName that is not a valid hostname' => [
                'neighbor' => ['sysName' => 'Edge Router', 'portId' => 'ge-0/0/0'],
                'device' => 'edge1',
                'port' => 'ge-0/0/0',
            ],
            'short sysName matches fqdn sysName with mydomain' => [
                'neighbor' => ['sysName' => 'core-sw', 'portId' => '1/1/1'],
                'device' => '192.0.2.60',
                'port' => '1/1/1',
                'config' => ['mydomain' => 'neighbor.test'],
            ],
            'duplicate sysName is ambiguous' => [
                'neighbor' => ['sysName' => 'dupe', 'portId' => 'eth0'],
                'device' => null,
                'port' => null,
            ],

            // ---- device by management ip ----
            'management ip matches hostname' => [
                'neighbor' => ['sysName' => 'unknown-ap', 'managementIp' => '192.0.2.50', 'portId' => 'eth0'],
                'device' => '192.0.2.50',
                'port' => 'eth0',
            ],
            'management ip matches device ip' => [
                'neighbor' => ['managementIp' => '192.0.2.1', 'portId' => 'Gi1/0/2'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/2',
            ],
            'ipv6 management ip matches device ip' => [
                'neighbor' => ['managementIp' => '2001:db8:0::2', 'portId' => 'xe-0/0/0'],
                'device' => 'dist1.neighbor.test',
                'port' => 'xe-0/0/0',
            ],
            'management ip assigned to a port' => [
                'neighbor' => ['managementIp' => '203.0.113.2', 'portId' => 'xe-0/0/0'],
                'device' => 'dist1.neighbor.test',
                'port' => 'xe-0/0/0',
            ],
            'management ip assigned to ports on two devices is ambiguous' => [
                'neighbor' => ['managementIp' => '198.51.100.254', 'portId' => 'vrrp1'],
                'device' => null,
                'port' => null,
            ],

            // ---- device by mac ----
            'port mac matches a port' => [
                'neighbor' => ['portId' => 'feed00050002', 'portIdSubtype' => LldpPortIdSubtype::MacAddress, 'portMac' => 'feed00050002'],
                'device' => '192.0.2.50',
                'port' => 'eth0',
            ],
            'chassis mac matches a port' => [
                'neighbor' => ['chassisMac' => 'feed00010105', 'portId' => 'Gi1/0/4'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/4',
            ],
            'mac on several ports of one device' => [
                'neighbor' => ['chassisMac' => 'feed0000aaaa', 'portId' => 'g2'],
                'device' => 'linksys.neighbor.test',
                'port' => 'g2',
            ],
            'mac on ports of two devices is ambiguous' => [
                'neighbor' => ['chassisMac' => 'feed5e000101', 'portId' => 'vrrp1'],
                'device' => null,
                'port' => null,
            ],
            'unknown neighbor' => [
                'neighbor' => ['sysName' => 'nobody.neighbor.test', 'managementIp' => '192.0.2.99', 'chassisMac' => 'feedffffffff', 'portId' => 'eth0'],
                'device' => null,
                'port' => null,
            ],

            // ---- port by port id ----
            'port id matches ifDescr' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'GigabitEthernet1/0/2'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/2',
            ],
            'port id match is case-insensitive' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'gi1/0/3'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/3',
            ],
            'port id of alias subtype matches ifAlias' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'server rack 3', 'portIdSubtype' => LldpPortIdSubtype::InterfaceAlias],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/3',
            ],
            'port id of name subtype does not match ifAlias' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'server rack 3'],
                'device' => 'core1.neighbor.test',
                'port' => null,
            ],
            'port id of mac subtype' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'feed00010105', 'portIdSubtype' => LldpPortIdSubtype::MacAddress, 'portMac' => 'feed00010105'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/5',
            ],
            'port id of mac subtype shared by all ports falls back to port description' => [
                'neighbor' => ['sysName' => 'linksys', 'portId' => 'feed0000aaaa', 'portIdSubtype' => LldpPortIdSubtype::MacAddress, 'portMac' => 'feed0000aaaa', 'portDescr' => 'g3'],
                'device' => 'linksys.neighbor.test',
                'port' => 'g3',
            ],
            'port id of network address subtype' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => '198.51.100.1', 'portIdSubtype' => LldpPortIdSubtype::NetworkAddress],
                'device' => 'core1.neighbor.test',
                'port' => 'Vl10',
            ],
            'numeric port id matches ifIndex' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => '4', 'portIdSubtype' => LldpPortIdSubtype::Local],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/4',
            ],
            'port description is preferred over numeric port id' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => '1', 'portIdSubtype' => LldpPortIdSubtype::Local, 'portDescr' => 'GigabitEthernet1/0/4'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/4',
            ],
            'deleted ports are ignored' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'Gi1/0/9'],
                'device' => 'core1.neighbor.test',
                'port' => null,
            ],
            'port is only searched on the found device' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'xe-0/0/0'],
                'device' => 'core1.neighbor.test',
                'port' => null,
            ],

            'port name is preferred over port mac' => [
                'neighbor' => ['sysName' => 'esx1', 'portId' => 'vmnic1', 'portMac' => 'feed000e0000'],
                'device' => 'esx1.neighbor.test',
                'port' => 'vmnic1',
            ],
            'port mac is preferred over port description' => [
                'neighbor' => ['sysName' => 'esx1', 'portId' => 'vmnic7', 'portMac' => 'feed000e0001', 'portDescr' => 'vmnic0'],
                'device' => 'esx1.neighbor.test',
                'port' => 'vmnic1',
            ],
            'port mac is used when the port name is unknown' => [
                'neighbor' => ['sysName' => 'esx1', 'portId' => 'vmnic7', 'portMac' => 'feed000e0001'],
                'device' => 'esx1.neighbor.test',
                'port' => 'vmnic1',
            ],
            'unknown neighbor found by port mac with a port name' => [
                'neighbor' => ['sysName' => 'esx-unknown', 'portId' => 'vmnic0', 'portMac' => 'feed000e0000'],
                'device' => 'esx1.neighbor.test',
                'port' => 'vmnic0',
            ],

            // ---- port by port description ----
            'port description matches ifDescr' => [
                'neighbor' => ['sysName' => 'core1', 'portId' => 'unknown', 'portDescr' => 'GigabitEthernet1/0/2'],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/2',
            ],
            'port description matches ifAlias' => [
                'neighbor' => ['sysName' => 'DIST-1', 'portDescr' => 'core1 Gi1/0/1'],
                'device' => 'dist1.neighbor.test',
                'port' => 'xe-0/0/1',
            ],
            'port description with surrounding whitespace' => [
                'neighbor' => ['sysName' => 'core1', 'portDescr' => ' GigabitEthernet1/0/2 '],
                'device' => 'core1.neighbor.test',
                'port' => 'Gi1/0/2',
            ],
            'port description shared by all ports is ambiguous' => [
                'neighbor' => ['sysName' => 'linksys', 'portDescr' => 'Ethernet Interface'],
                'device' => 'linksys.neighbor.test',
                'port' => null,
            ],
            'port id is preferred over a port description shared by all ports' => [
                'neighbor' => ['sysName' => 'linksys', 'portId' => 'g2', 'portDescr' => 'Ethernet Interface'],
                'device' => 'linksys.neighbor.test',
                'port' => 'g2',
            ],

            // ---- port by OS specific rules (NeighborPortResolution) ----
            'xos slot:port' => [
                'neighbor' => ['sysName' => 'xos1', 'portId' => '2:5', 'portIdSubtype' => LldpPortIdSubtype::Local],
                'device' => 'xos1.neighbor.test',
                'port' => 'Slot-2 Port 5',
            ],
            'xos standalone port is slot 1' => [
                'neighbor' => ['sysName' => 'xos1', 'portId' => '5', 'portIdSubtype' => LldpPortIdSubtype::Local],
                'device' => 'xos1.neighbor.test',
                'port' => 'Slot-1 Port 5',
            ],
            'xos named port uses generic matching' => [
                'neighbor' => ['sysName' => 'xos1', 'portId' => 'Mgmt'],
                'device' => 'xos1.neighbor.test',
                'port' => 'Mgmt',
            ],
            'calix port number' => [
                'neighbor' => ['sysName' => 'calix1', 'portId' => '3', 'portIdSubtype' => LldpPortIdSubtype::Local],
                'device' => 'calix1.neighbor.test',
                'port' => 'EthPort 3',
            ],
            'netgear GS108T g port' => [
                'neighbor' => ['sysName' => 'gs108t', 'sysDescr' => 'Smart Switch', 'portId' => 'g2', 'portDescr' => 'Port #2'],
                'device' => 'gs108t.neighbor.test',
                'port' => 'Port 2 Gigabit Ethernet',
            ],
            'netgear g port from another model' => [
                'neighbor' => ['sysName' => 'gs108t', 'sysDescr' => 'Some Other Switch', 'portId' => 'g2', 'portDescr' => 'Port #2'],
                'device' => 'gs108t.neighbor.test',
                'port' => null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $neighbor
     * @param  array<string, mixed>  $config
     */
    #[DataProvider('neighborProvider')]
    public function testFindNeighbor(array $neighbor, ?string $device, ?string $port, array $config = []): void
    {
        LibrenmsConfig::set('mydomain', null);
        foreach ($config as $key => $value) {
            LibrenmsConfig::set($key, $value);
        }

        $this->createNetwork();

        $neighbor = new Neighbor(...['protocol' => 'lldp', 'localPortId' => null, ...$neighbor]);
        $finder = new NeighborFinder;

        $foundDevice = $finder->findDevice($neighbor);
        $this->assertSame($device, $foundDevice?->hostname, 'Found the wrong device');

        $foundPort = $foundDevice ? $finder->findPort($neighbor, $foundDevice) : null;
        $this->assertSame($port, $foundPort?->ifName, 'Found the wrong port');
    }

    public function testDeviceLookupsAreCached(): void
    {
        $this->createNetwork();
        $finder = new NeighborFinder;
        $neighbor = new Neighbor('lldp', null, sysName: 'DIST-1', managementIp: '192.0.2.99', chassisMac: 'feedffffffff');

        $this->assertSame('dist1.neighbor.test', $finder->findDevice($neighbor)?->hostname);

        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $this->assertSame('dist1.neighbor.test', $finder->findDevice(new Neighbor('cdp', 5, sysName: 'DIST-1', managementIp: '192.0.2.99', chassisMac: 'feedffffffff'))?->hostname);
        $this->assertSame(0, $queries, 'The same neighbor identifiers should not query the database again');
    }

    private function createNetwork(): void
    {
        foreach (self::NETWORK as $hostname => $attributes) {
            $device = Device::factory()->create([
                'hostname' => $attributes['hostname'] ?? $hostname,
                'sysName' => $attributes['sysName'],
                'sysDescr' => $attributes['sysDescr'] ?? null,
                'ip' => $attributes['ip'] ?? null,
                'os' => $attributes['os'] ?? 'generic',
            ]);

            foreach ($attributes['ports'] as $ifName => $port_attributes) {
                $port = Port::factory()->create([
                    'device_id' => $device->device_id,
                    'ifName' => $ifName,
                    'ifDescr' => $port_attributes['ifDescr'] ?? $ifName,
                    'ifIndex' => $port_attributes['ifIndex'],
                    'ifAlias' => $port_attributes['ifAlias'] ?? null,
                    'ifPhysAddress' => $port_attributes['ifPhysAddress'] ?? null,
                    'deleted' => $port_attributes['deleted'] ?? 0,
                ]);

                if (isset($port_attributes['ipv4'])) {
                    Ipv4Address::factory()->create([
                        'port_id' => $port->port_id,
                        'ipv4_address' => $port_attributes['ipv4'],
                        'ipv4_prefixlen' => 24,
                        'context_name' => '',
                    ]);
                }
            }
        }
    }
}
