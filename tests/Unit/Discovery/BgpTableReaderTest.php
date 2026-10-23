<?php

/**
 * BgpTableReaderTest.php
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

use LibreNMS\Discovery\Bgp\BgpTableReader;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BgpTableReaderTest extends TestCase
{
    /**
     * @return array<string, array{layout: string[], index: string, expected: array<string, string>|null}>
     */
    public static function indexProvider(): array
    {
        return [
            'address with length' => ['layout' => ['skip', 'address'], 'index' => '1.1.4.192.0.2.1', 'expected' => ['address' => '192.0.2.1']],
            'address without length (FRR)' => ['layout' => ['skip', 'address'], 'index' => '1.1.192.0.2.1', 'expected' => ['address' => '192.0.2.1']],
            'address starting with its own length' => ['layout' => ['address'], 'index' => '1.4.4.0.2.1', 'expected' => ['address' => '4.0.2.1']],
            'address without length starting with 4' => ['layout' => ['address'], 'index' => '1.4.0.2.1', 'expected' => ['address' => '4.0.2.1']],
            'ipv6 address' => ['layout' => ['address'], 'index' => '2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.1', 'expected' => ['address' => '2001:db8::1']],
            'ipv6 link local with zone' => ['layout' => ['address'], 'index' => '4.20.254.128.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.3', 'expected' => ['address' => 'fe80::1', 'iface' => '3']],
            'address then afi and safi' => ['layout' => ['vrf', 'address', 'afi', 'safi'], 'index' => '7.1.4.192.0.2.1.2.128', 'expected' => ['vrf' => '7', 'address' => '192.0.2.1', 'afi' => 'ipv6', 'safi' => 'vpn']],
            'ip' => ['layout' => ['ip'], 'index' => '4.0.2.1', 'expected' => ['address' => '4.0.2.1']],
            'ip with length' => ['layout' => ['skip', 'vrf', 'ip'], 'index' => '0.3.4.192.0.2.1', 'expected' => ['vrf' => '3', 'address' => '192.0.2.1']],
            'local then remote address (Juniper)' => ['layout' => ['skip', 'skip_address', 'address'], 'index' => '0.1.192.168.99.24.1.4.168.99.25', 'expected' => ['address' => '4.168.99.25']],
            'unknown address type' => ['layout' => ['address'], 'index' => '16.4.192.0.2.1', 'expected' => null],
            'address too short' => ['layout' => ['address'], 'index' => '1.4.192.0', 'expected' => null],
        ];
    }

    /**
     * @param  string[]  $layout
     * @param  array<string, string>|null  $expected
     */
    #[DataProvider('indexProvider')]
    public function testIndexLayout(array $layout, string $index, ?array $expected): void
    {
        $reader = new FakeBgpTableReader(['peers' => [['index_layout' => $layout, 'remote_as' => 'TEST-MIB::remoteAs']]], [
            'remoteAs' => [$index => '65001'],
        ]);

        $peers = $reader->read('')['peers'];

        if ($expected === null) {
            $this->assertSame([], $peers);

            return;
        }

        $this->assertCount(1, $peers);
        $this->assertSame($expected, array_map(strval(...), array_diff_key($peers[0], ['remote_as' => 1])));
    }

    public function testValuesAreDecoded(): void
    {
        $reader = new FakeBgpTableReader(['peers' => [[
            'index_layout' => ['ip'],
            'remote_as' => 'TEST-MIB::remoteAs',
            'state' => 'TEST-MIB::state',
            'state_map' => ['openWait' => 'active'],
            'admin_status' => 'TEST-MIB::shutdown',
            'admin_status_map' => ['true' => 'stop', 'false' => 'start'],
            'local_address' => 'TEST-MIB::localAddr',
            'last_error' => 'TEST-MIB::lastError',
            'in_messages' => 'TEST-MIB::octets',
            'descr' => 'a literal',
        ]]], [
            'remoteAs' => ['192.0.2.1' => '-94967294', '192.0.2.2' => '65001', '192.0.2.3' => '65001'],
            'state' => ['192.0.2.1' => 'openSent', '192.0.2.2' => 'openWait', '192.0.2.3' => 'closed'],
            'shutdown' => ['192.0.2.1' => 'true', '192.0.2.2' => 'false'],
            'localAddr' => ['192.0.2.1' => 'C0 00 02 FE', '192.0.2.2' => 'fe80::1'],
            'lastError' => ['192.0.2.1' => '04 0B ', '192.0.2.2' => "\x06\x02", '192.0.2.3' => ''],
            'octets' => ['192.0.2.1' => (string) (2 ** 32 + 5)],
        ]);

        $peers = $reader->read('')['peers'];
        $this->assertCount(3, $peers);
        [$first, $second, $third] = $peers;

        $this->assertSame(4200000002, $first['remote_as']);
        $this->assertSame('opensent', $first['state']);
        $this->assertSame('stop', $first['admin_status']);
        $this->assertSame('192.0.2.254', $first['local_address']);
        $this->assertSame([4, 11], [$first['last_error_code'], $first['last_error_subcode']]);
        $this->assertSame(5, $first['in_messages']);
        $this->assertSame('a literal', $first['descr']);

        $this->assertSame('active', $second['state']);
        $this->assertSame('start', $second['admin_status']);
        $this->assertSame('fe80:0000:0000:0000:0000:0000:0000:0001', $second['local_address']);
        $this->assertSame([6, 2], [$second['last_error_code'], $second['last_error_subcode']]);

        $this->assertSame('idle', $third['state'], 'Unknown states are idle');
        $this->assertArrayNotHasKey('last_error_code', $third);
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function counterProvider(): array
    {
        return [
            '32 bit' => ['4294967295', 4294967295],
            'wrapped 32 bit' => ['4294967301', 5],
            'above PHP_INT_MAX' => ['18446744071926153491', 2511569171],
            'PHP_INT_MAX + 1' => ['9223372036854775808', 0],
            'max 64 bit' => ['18446744073709551615', 4294967295],
            'negative 32 bit' => ['-5', 4294967291],
        ];
    }

    #[DataProvider('counterProvider')]
    public function testCountersKeepTheLow32Bits(string $value, int $expected): void
    {
        $reader = new FakeBgpTableReader(['peers' => [['index_layout' => ['ip'], 'remote_as' => 'TEST-MIB::remoteAs', 'in_messages' => 'TEST-MIB::octets']]], [
            'remoteAs' => ['192.0.2.1' => '65001'],
            'octets' => ['192.0.2.1' => $value],
        ]);

        $this->assertSame($expected, $reader->read('')['peers'][0]['in_messages']);
    }

    /**
     * Scoped and unnumbered sessions are identified by interface, so parallel links to one neighbor stay separate
     */
    public function testLinkLocalAndUnnumberedSessionsAreKeptPerInterface(): void
    {
        $fe80 = '254.128.0.0.0.0.0.0.42.153.58.255.254.30.176.127';
        $scoped = new FakeBgpTableReader(['peers' => [['index_layout' => ['skip', 'address'], 'remote_as' => 'TEST-MIB::remoteAs']]], [
            'remoteAs' => ["1.4.20.$fe80.0.0.15.161" => '65001', "1.4.20.$fe80.0.0.15.162" => '65001', '1.1.4.192.0.2.1' => '65002'],
        ]);

        $peers = $scoped->read('')['peers'];
        $this->assertSame([['fe80::2a99:3aff:fe1e:b07f', 4001], ['fe80::2a99:3aff:fe1e:b07f', 4002], ['192.0.2.1', null]], array_map(fn ($peer) => [(string) $peer['address'], $peer['iface'] ?? null], $peers));
        $this->assertSame([true, true, false], array_map(BgpTableReader::identifiedByInterface(...), $peers));

        $unnumbered = new FakeBgpTableReader(['peers' => [['index_layout' => ['vrf'], 'address' => 'TEST-MIB::remoteAddr', 'iface' => 'TEST-MIB::ifIndex', 'remote_as' => 'TEST-MIB::remoteAs']]], [
            'remoteAs' => ['254.0.0.0.0.27' => '65001', '254.0.0.0.0.28' => '65001', '254.1.192.0.2.1' => '65002'],
            'remoteAddr' => ['254.0.0.0.0.27' => 'Unknown', '254.0.0.0.0.28' => 'fe80::1', '254.1.192.0.2.1' => '192.0.2.1'],
            'ifIndex' => ['254.0.0.0.0.27' => '27', '254.0.0.0.0.28' => '28', '254.1.192.0.2.1' => '0'],
        ]);

        $peers = $unnumbered->read('')['peers'];
        $this->assertCount(3, $peers, 'Sessions that have not learned an address are kept');
        $this->assertSame([true, true, false], array_map(BgpTableReader::identifiedByInterface(...), $peers));
        $this->assertArrayNotHasKey('address', $peers[0]);
    }

    public function testAddressFamilyTablesCanBeLimitedToAnIpVersion(): void
    {
        $reader = new FakeBgpTableReader([
            'peers' => [['index_layout' => ['address'], 'remote_as' => 'TEST-MIB::remoteAs']],
            'address_families' => [
                ['index_layout' => ['address'], 'ip_version' => 4, 'afi' => 'ipv4', 'safi' => 'unicast', 'accepted' => 'TEST-MIB::v4Prefixes'],
                ['index_layout' => ['address'], 'ip_version' => 6, 'afi' => 'ipv6', 'safi' => 'unicast', 'accepted' => 'TEST-MIB::v6Prefixes'],
            ],
        ], [
            'remoteAs' => ['1.4.192.0.2.1' => '65001', '2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.1' => '65002'],
            'v4Prefixes' => ['1.4.192.0.2.1' => '10', '2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.1' => '0'],
            'v6Prefixes' => ['1.4.192.0.2.1' => '0', '2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.1' => '20'],
        ]);

        $families = array_map(fn ($family) => "{$family['address']} {$family['afi']} {$family['accepted']}", $reader->read('')['address_families']);

        $this->assertSame(['192.0.2.1 ipv4 10', '2001:db8::1 ipv6 20'], $families);
    }

    /**
     * Huawei reports a row per address family and keeps counters and descriptions in other tables
     */
    public function testTablesAreMergedByVrfAndAddress(): void
    {
        $reader = new FakeBgpTableReader(['peers' => [
            ['index_layout' => ['vrf', 'afi', 'safi', 'address'], 'remote_as' => 'TEST-MIB::remoteAs', 'state' => 'TEST-MIB::state'],
            ['index_layout' => ['skip', 'vrf', 'ip'], 'in_updates' => 'TEST-MIB::inUpdates', 'state' => 'TEST-MIB::otherState'],
            ['index_layout' => ['vrf', 'address'], 'descr' => 'TEST-MIB::descr'],
        ]], [
            'remoteAs' => ['0.1.1.1.4.192.0.2.1' => '65001', '0.1.128.1.4.192.0.2.1' => '65001', '5.1.1.1.4.192.0.2.1' => '65002'],
            'state' => ['0.1.1.1.4.192.0.2.1' => 'established', '0.1.128.1.4.192.0.2.1' => 'established', '5.1.1.1.4.192.0.2.1' => 'active'],
            'inUpdates' => ['0.0.4.192.0.2.1' => '10', '0.5.4.192.0.2.1' => '20', '0.9.4.192.0.2.9' => '30'],
            'otherState' => ['0.0.4.192.0.2.1' => 'idle'],
            'descr' => ['0.1.4.192.0.2.1' => 'public peer', '5.1.4.192.0.2.9' => 'unknown peer'],
        ]);

        $peers = $reader->read('')['peers'];

        $this->assertSame([
            ['0', '192.0.2.1', 65001, 'established', 10, 'public peer'],
            ['5', '192.0.2.1', 65002, 'active', 20, null],
        ], array_map(fn ($peer) => [$peer['vrf'], (string) $peer['address'], $peer['remote_as'], $peer['state'], $peer['in_updates'], $peer['descr'] ?? null], $peers));
        $this->assertTrue($reader->reportsAllVrfs());
    }

    public function testBgp4MibIsOnlyReadWhenNoTableReportsSessions(): void
    {
        $vendor = new FakeBgpTableReader(['peers' => [['index_layout' => ['address'], 'remote_as' => 'TEST-MIB::remoteAs']]], [
            'remoteAs' => ['1.4.192.0.2.1' => '65001'],
            'bgpPeerRemoteAs' => ['192.0.2.9' => '65009'],
        ]);
        $this->assertSame(['192.0.2.1'], array_map(fn ($peer) => (string) $peer['address'], $vendor->read('')['peers']));
        $this->assertNotContains('BGP4-MIB::bgpPeerRemoteAs', $vendor->walked);
        $this->assertFalse($vendor->reportsAllVrfs());

        // names alone do not make a session, so BGP4-MIB is read and the names are merged in
        $names = new FakeBgpTableReader(['peers' => [['index_layout' => ['ip'], 'descr' => 'TEST-MIB::name']]], [
            'name' => ['192.0.2.9' => 'peer nine', '192.0.2.8' => 'no session'],
            'bgpPeerRemoteAs' => ['192.0.2.9' => '65009'],
            'bgpPeerState' => ['192.0.2.9' => 'established'],
        ]);
        $peers = $names->read('')['peers'];
        $this->assertCount(1, $peers);
        $this->assertSame(['peer nine', 65009, 'established'], [$peers[0]['descr'], $peers[0]['remote_as'], $peers[0]['state']]);
    }

    public function testAddressFamilies(): void
    {
        $reader = new FakeBgpTableReader([
            'peers' => [['index_layout' => ['skip', 'skip_address', 'address'], 'remote_as' => 'TEST-MIB::remoteAs', 'peer_index' => 'TEST-MIB::peerIndex']],
            'address_families' => [
                ['index_layout' => ['peer_index', 'afi', 'safi'], 'accepted' => 'TEST-MIB::accepted'],
                ['index_layout' => ['ip'], 'fallback' => true, 'afi' => 'ipv4', 'safi' => 'unicast', 'accepted' => 'TEST-MIB::fallbackAccepted'],
            ],
        ], [
            'remoteAs' => ['0.1.192.0.2.254.1.192.0.2.1' => '65001', '0.1.192.0.2.254.1.192.0.2.2' => '65002'],
            'peerIndex' => ['0.1.192.0.2.254.1.192.0.2.1' => '3', '0.1.192.0.2.254.1.192.0.2.2' => '4'],
            'accepted' => ['3.1.1' => '20', '3.25.65' => '5', '4.2.128' => '7', '9.1.1' => '1'],
            'fallbackAccepted' => ['192.0.2.1' => '99'],
        ]);

        $families = array_map(fn ($family) => "{$family['address']} {$family['afi']} {$family['safi']} {$family['accepted']}", $reader->read('')['address_families']);

        $this->assertSame(['192.0.2.1 ipv4 unicast 20', '192.0.2.1 l2vpn vpls 5', '192.0.2.2 ipv6 vpn 7'], $families);
        $this->assertNotContains('TEST-MIB::fallbackAccepted', $reader->walked);
    }
}

/**
 * Serves walks from canned column data keyed by column name and numeric index
 */
class FakeBgpTableReader extends BgpTableReader
{
    /** @var string[] */
    public array $walked = [];

    /**
     * @param  array{peers?: array<int, array<string, mixed>>, address_families?: array<int, array<string, mixed>>}  $definition
     * @param  array<string, array<string, string>>  $columns
     */
    public function __construct(array $definition, private readonly array $columns)
    {
        parent::__construct($definition);
    }

    protected function walk(string $context, array $oids): array
    {
        $rows = [];
        foreach ($oids as $oid) {
            $this->walked[] = $oid;
            $column = substr($oid, strpos($oid, '::') + 2);
            foreach ($this->columns[$column] ?? [] as $index => $value) {
                $rows[$index][$column] = $value;
            }
        }

        return $rows;
    }
}
