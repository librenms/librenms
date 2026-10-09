<?php

/**
 * PortFinderTest.php
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

namespace LibreNMS\Tests\Unit\Discovery;

use App\Models\Port;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\PortFinder;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Local port mapping for LLDP, resolved against the ports in PORTS.
 *
 * To add a case, add an entry to lldpLocalPortProvider():
 *   'portNum' => lldpRemLocalPortNum
 *   'entry'   => lldpLocPortTable row for that port number
 *   'bridge'  => dot1dBasePortIfIndex (bridge port => ifIndex)
 *   'port'    => expected ifName, or null if no port should be found
 */
final class PortFinderTest extends TestCase
{
    /**
     * ifName => attributes, ifDescr defaults to the ifName
     */
    private const PORTS = [
        'Gi1/0/1' => ['ifIndex' => 10101, 'ifDescr' => 'GigabitEthernet1/0/1'],
        'Gi1/0/2' => ['ifIndex' => 10102, 'ifDescr' => 'GigabitEthernet1/0/2', 'ifAlias' => 'uplink'],
        'Gi1/0/3' => ['ifIndex' => 10103, 'ifDescr' => 'GigabitEthernet1/0/3'],
        'port 1' => ['ifIndex' => 1, 'ifDescr' => 'Ethernet Interface'],
        'port 2' => ['ifIndex' => 2, 'ifDescr' => 'Ethernet Interface'],
        'dup-a' => ['ifIndex' => 50],
        'dup-b' => ['ifIndex' => 50],
    ];

    /**
     * @return array<string, array{portNum: int, entry: array<string, mixed>, bridge: array<int, int>, port: ?string}>
     */
    public static function lldpLocalPortProvider(): array
    {
        return [
            'port id matches ifName' => [
                'portNum' => 3,
                'entry' => ['lldpLocPortId' => 'Gi1/0/1'],
                'bridge' => [],
                'port' => 'Gi1/0/1',
            ],
            'port id matches ifDescr' => [
                'portNum' => 3,
                'entry' => ['lldpLocPortId' => 'GigabitEthernet1/0/3'],
                'bridge' => [],
                'port' => 'Gi1/0/3',
            ],
            'hex encoded port id' => [
                'portNum' => 3,
                'entry' => ['lldpLocPortId' => '47 69 31 2F 30 2F 31 00'],
                'bridge' => [],
                'port' => 'Gi1/0/1',
            ],
            'port id is preferred over port number' => [
                'portNum' => 1,
                'entry' => ['lldpLocPortId' => 'Gi1/0/2'],
                'bridge' => [],
                'port' => 'Gi1/0/2',
            ],
            'port number is an ifIndex' => [
                'portNum' => 10103,
                'entry' => ['lldpLocPortId' => 'aa:bb:cc:dd:ee:ff'],
                'bridge' => [],
                'port' => 'Gi1/0/3',
            ],
            'port number is a bridge port' => [
                'portNum' => 3,
                'entry' => [],
                'bridge' => [1 => 10101, 2 => 10102, 3 => 10103],
                'port' => 'Gi1/0/3',
            ],
            'bridge port maps to a different port than the same ifIndex' => [
                'portNum' => 1,
                'entry' => [],
                'bridge' => [1 => 10102],
                'port' => 'Gi1/0/2',
            ],
            'port number missing from the bridge table is used as an ifIndex (Junos, Scalance)' => [
                'portNum' => 10103,
                'entry' => [],
                'bridge' => [1 => 10101],
                'port' => 'Gi1/0/3',
            ],
            'port number not found falls back to the port description' => [
                'portNum' => 99,
                'entry' => ['lldpLocPortDesc' => 'GigabitEthernet1/0/3'],
                'bridge' => [1 => 10101],
                'port' => 'Gi1/0/3',
            ],
            'port id matches ifAlias' => [
                'portNum' => 99,
                'entry' => ['lldpLocPortId' => 'uplink'],
                'bridge' => [],
                'port' => 'Gi1/0/2',
            ],
            'ambiguous port id falls back to port number' => [
                'portNum' => 10101,
                'entry' => ['lldpLocPortId' => 'Ethernet Interface'],
                'bridge' => [],
                'port' => 'Gi1/0/1',
            ],
            'ambiguous port description is skipped' => [
                'portNum' => 99,
                'entry' => ['lldpLocPortDesc' => 'Ethernet Interface'],
                'bridge' => [],
                'port' => null,
            ],
            'duplicate ifIndex is skipped' => [
                'portNum' => 50,
                'entry' => [],
                'bridge' => [],
                'port' => null,
            ],
            'nothing matches' => [
                'portNum' => 99,
                'entry' => ['lldpLocPortId' => 'unknown', 'lldpLocPortDesc' => 'unknown'],
                'bridge' => [],
                'port' => null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<int, int>  $bridge
     */
    #[DataProvider('lldpLocalPortProvider')]
    public function testFindLldpLocalPort(int $portNum, array $entry, array $bridge, ?string $port): void
    {
        $this->assertSame($port, $this->portFinder()->findLldpLocalPort($portNum, $entry, $bridge)?->ifName);
    }

    public function testByIfIndex(): void
    {
        $finder = $this->portFinder();

        $this->assertSame('Gi1/0/1', $finder->byIfIndex(10101)?->ifName);
        $this->assertSame('Gi1/0/1', $finder->byIfIndex('10101')?->ifName);
        $this->assertNull($finder->byIfIndex(50), 'Duplicate ifIndex should not match');
        $this->assertNull($finder->byIfIndex(12345));
        $this->assertNull($finder->byIfIndex(null));
        $this->assertNull($finder->byIfIndex(''));
        $this->assertNull($finder->byIfIndex('Gi1/0/1'));
    }

    private function portFinder(): PortFinder
    {
        $ports = new Collection;
        $port_id = 1;
        foreach (self::PORTS as $ifName => $attributes) {
            $ports->push((new Port)->forceFill([
                'port_id' => $port_id++,
                'device_id' => 1,
                'ifName' => $ifName,
                'ifDescr' => $attributes['ifDescr'] ?? $ifName,
                'ifAlias' => $attributes['ifAlias'] ?? null,
                'ifIndex' => $attributes['ifIndex'],
            ]));
        }

        return new PortFinder($ports);
    }
}
