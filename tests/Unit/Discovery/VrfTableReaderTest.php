<?php

/**
 * VrfTableReaderTest.php
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

use LibreNMS\Discovery\Vrf\VrfTableReader;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class VrfTableReaderTest extends TestCase
{
    /**
     * @return array<string, array{layout: string[], index: string, expected: array<string, string>|null}>
     */
    public static function indexProvider(): array
    {
        return [
            'vrf' => ['layout' => ['vrf'], 'index' => '3', 'expected' => ['vrf' => '3']],
            'vrf name' => ['layout' => ['vrf_name'], 'index' => '4.109.103.109.116', 'expected' => ['vrf' => '4.109.103.109.116', 'name' => 'mgmt']],
            'vrf name and ifIndex' => ['layout' => ['vrf_name', 'ifIndex'], 'index' => '5.116.101.115.116.49.198', 'expected' => ['vrf' => '5.116.101.115.116.49', 'name' => 'test1', 'ifIndex' => '198']],
            'vrf and ifIndex' => ['layout' => ['vrf', 'ifIndex'], 'index' => '2.35', 'expected' => ['vrf' => '2', 'ifIndex' => '35']],
            'context' => ['layout' => ['context'], 'index' => '5.86.82.70.45.65', 'expected' => ['context' => 'VRF-A']],
            'skip' => ['layout' => ['skip', 'vrf'], 'index' => '9.4', 'expected' => ['vrf' => '4']],
            'trailing parts are ignored' => ['layout' => ['vrf'], 'index' => '1.1.4.192.0.2.1', 'expected' => ['vrf' => '1']],
            'empty layout ignores the index' => ['layout' => [], 'index' => '1.4.192.0.2.1', 'expected' => []],
            'string too short' => ['layout' => ['vrf_name'], 'index' => '5.116.101', 'expected' => null],
            'missing part' => ['layout' => ['vrf', 'ifIndex'], 'index' => '2', 'expected' => null],
        ];
    }

    /**
     * @param  string[]  $layout
     * @param  array<string, string>|null  $expected
     */
    #[DataProvider('indexProvider')]
    public function testIndexLayout(array $layout, string $index, ?array $expected): void
    {
        $this->assertSame($expected, (new VrfTableReader)->parseIndex($layout, explode('.', $index)));
    }

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function routeDistinguisherProvider(): array
    {
        return [
            'text' => ['65000:100', '65000:100'],
            'quoted text' => ['"10.246.0.3:10001"', '10.246.0.3:10001'],
            'text with trailing null (VRP)' => ['36 35 33 30 31 3A 31 00', '65301:1'],
            'type 0 (TiMOS, Ironware)' => ['00 00 30 39 00 00 00 6F', '12345:111'],
            'type 0 ending in null' => ['00 00 30 39 00 00 01 00', '12345:256'],
            'type 1' => ['00 01 C0 00 02 01 00 64', '192.0.2.1:100'],
            'type 2' => ['00 02 FA 56 EA 00 00 0A', '4200000000:10'],
            'not set' => ['00 00 00 00 00 00 00 00', null],
            'empty' => ['', null],
            'unknown type' => ['00 09 00 00 00 00 00 01', null],
        ];
    }

    #[DataProvider('routeDistinguisherProvider')]
    public function testRouteDistinguisher(string $value, ?string $expected): void
    {
        $this->assertSame($expected, (new VrfTableReader)->decodeRouteDistinguisher($value));
    }

    public function testStandardMibsAreReadWhenTheOsHasNoTables(): void
    {
        $reader = new FakeVrfTableReader([], [
            'mplsL3VpnVrfRD' => ['4.109.103.109.116' => '', '5.116.101.115.116.49' => '65301:1'],
            'mplsL3VpnVrfDescription' => ['5.116.101.115.116.49' => 'Test VRF'],
            'mplsL3VpnIfVpnClassification' => ['5.116.101.115.116.49.198' => 'enterprise', '5.116.101.115.116.49.199' => 'enterprise'],
        ]);

        $this->assertSame([
            '4.109.103.109.116' => ['vrf_oid' => '4.109.103.109.116', 'name' => 'mgmt', 'rd' => null, 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => []],
            '5.116.101.115.116.49' => ['vrf_oid' => '5.116.101.115.116.49', 'name' => 'test1', 'rd' => '65301:1', 'description' => 'Test VRF', 'local_as' => null, 'context' => null, 'ifIndexes' => [198, 199]],
        ], $reader->read());
        $this->assertNotContains('MPLS-VPN-MIB::mplsVpnVrfDescription', $reader->walked, 'MPLS-VPN-MIB is only read when MPLS-L3VPN-STD-MIB has no VRFs');
    }

    public function testOsTablesReplaceTheStandardMibs(): void
    {
        $reader = new FakeVrfTableReader([
            [
                'index_layout' => ['vrf'],
                'name' => 'TIMETRA-VRTR-MIB::vRtrName',
                'local_as' => 'TIMETRA-VRTR-MIB::vRtrAS4Byte',
                'rd' => 'TIMETRA-VRTR-MIB::vRtrRouteDistinguisher',
            ],
            [
                'index_layout' => ['vrf', 'ifIndex'],
                'walk' => 'TIMETRA-VRTR-MIB::vRtrIfName',
            ],
        ], [
            'vRtrName' => ['1' => 'Base', '2' => 'vprn111', '254' => '****', '255' => '****'],
            'vRtrAS4Byte' => ['1' => '12345', '2' => '12345', '254' => '0'],
            'vRtrRouteDistinguisher' => ['1' => '00 00 00 00 00 00 00 00', '2' => '00 00 30 39 00 00 00 6F'],
            'vRtrIfName' => ['1.1' => 'system', '1.3' => 'to-core', '3.7' => 'unknown vrf'],
        ]);

        $vrfs = $reader->read();

        $this->assertSame(['1', '2', '254', '255'], array_column($vrfs, 'vrf_oid'), 'Interfaces of unknown VRFs do not create VRFs, VRFs of one table are not merged by name');
        $this->assertNull($vrfs['254']['local_as'], 'AS 0 is not set');
        $this->assertSame(['vrf_oid' => '1', 'name' => 'Base', 'rd' => null, 'description' => '', 'local_as' => 12345, 'context' => null, 'ifIndexes' => [1, 3]], $vrfs['1']);
        $this->assertSame('12345:111', $vrfs['2']['rd']);
        $this->assertEmpty(array_filter($reader->walked, fn (string $oid) => str_starts_with($oid, 'MPLS')), 'Standard MIBs are not read');
    }

    public function testMembershipAndContextsByName(): void
    {
        $reader = new FakeVrfTableReader([
            ['index_layout' => ['vrf_name'], 'rd' => 'ARISTA-VRF-MIB::aristaVrfRouteDistinguisher'],
            ['index_layout' => ['ifIndex'], 'name' => 'ARISTA-VRF-MIB::aristaVrfIfMembership'],
            ['index_layout' => ['context'], 'name' => 'CISCO-CONTEXT-MAPPING-MIB::cContextMappingVrfName'],
        ], [
            'aristaVrfRouteDistinguisher' => ['3.77.103.116' => '62003:1'],
            'aristaVrfIfMembership' => ['999001' => 'Mgt', '999002' => 'Lab'],
            'cContextMappingVrfName' => ['7.99.116.120.45.109.103.116' => 'mgt', '7.100.101.102.97.117.108.116' => 'default', '6.118.108.97.110.45.49' => ''],
        ]);

        $this->assertSame([
            '3.77.103.116' => ['vrf_oid' => '3.77.103.116', 'name' => 'Mgt', 'rd' => '62003:1', 'description' => '', 'local_as' => null, 'context' => 'ctx-mgt', 'ifIndexes' => [999001]],
            'Lab' => ['vrf_oid' => 'Lab', 'name' => 'Lab', 'rd' => null, 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => [999002]],
        ], $reader->read(), 'VRFs only known by name use the name as their index, the default VRF and contexts without a VRF are ignored');
    }

    public function testFallbackAddsMembershipToVrfsByName(): void
    {
        // Cisco C8xx routers report VRFs in MPLS-VPN-MIB, but their interfaces only in CISCO-VRF-MIB
        $reader = new FakeVrfTableReader([
            ['index_layout' => ['vrf'], 'fallback' => true, 'name' => 'CISCO-VRF-MIB::cvVrfName', 'skip_regex' => ['name' => '/Platform_iVRF/']],
            ['index_layout' => ['vrf', 'ifIndex'], 'fallback' => true, 'walk' => 'CISCO-VRF-MIB::cvVrfInterfaceType'],
        ], [
            'mplsVpnVrfDescription' => ['3.76.65.78' => '65000:10'],
            'cvVrfName' => ['1' => 'LAN', '2' => '__Platform_iVRF:_ID00_', '3' => 'GUEST'],
            'cvVrfInterfaceType' => ['1.10' => 'vrfEdge', '2.11' => 'vrfEdge', '3.12' => 'vrfEdge'],
        ]);

        $this->assertSame([
            '3.76.65.78' => ['vrf_oid' => '3.76.65.78', 'name' => 'LAN', 'rd' => '65000:10', 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => [10]],
            '3' => ['vrf_oid' => '3', 'name' => 'GUEST', 'rd' => null, 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => [12]],
        ], $reader->read());
    }

    public function testFallbackIsNotReadWhenVrfsHaveMembers(): void
    {
        $reader = new FakeVrfTableReader([
            ['index_layout' => ['vrf'], 'fallback' => true, 'name' => 'CISCO-VRF-MIB::cvVrfName'],
        ], [
            'mplsL3VpnVrfRD' => ['3.76.65.78' => '65000:10'],
            'mplsL3VpnIfVpnClassification' => ['3.76.65.78.10' => 'enterprise'],
        ]);

        $reader->read();

        $this->assertNotContains('CISCO-VRF-MIB::cvVrfName', $reader->walked);
    }

    public function testVrfFromValueWithSkipAndValueMap(): void
    {
        $reader = new FakeVrfTableReader([[
            'vrf' => 'FIREBRICK-BGP-MIB::fbBgpPeerTableId',
            'name' => 'FIREBRICK-BGP-MIB::fbBgpPeerTableId',
            'name_map' => ['44' => 'customers'],
            'description' => 'routing table',
            'skip_regex' => ['vrf' => '/^0$/'],
        ]], [
            'fbBgpPeerTableId' => ['1.4.10.2.3.4' => '0', '1.4.10.2.3.5' => '44', '2.16.253.1.8.176.0.0.0.251.0.0.0.0.0.0.0.1' => '44'],
        ]);

        $this->assertSame([
            '44' => ['vrf_oid' => '44', 'name' => 'customers', 'rd' => null, 'description' => 'routing table', 'local_as' => null, 'context' => null, 'ifIndexes' => []],
        ], $reader->read());
    }
}

class FakeVrfTableReader extends VrfTableReader
{
    /** @var string[] */
    public array $walked = [];

    /**
     * @param  array<int, array<string, mixed>>  $tables
     * @param  array<string, array<int|string, string>>  $columns
     */
    public function __construct(array $tables, private readonly array $columns)
    {
        parent::__construct($tables);
    }

    protected function walk(array $oids): array
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
