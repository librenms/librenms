<?php

/**
 * NeighborParserTest.php
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

use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * To add a case, add an entry to lldpEntryProvider():
 *   'entry'    => lldpRemTable columns as returned by snmp
 *   'expected' => Neighbor properties that should be set, unlisted properties are not checked
 */
final class NeighborParserTest extends TestCase
{
    /**
     * @return array<string, array{entry: array<string, mixed>, expected: array<string, mixed>, managementIp?: string}>
     */
    public static function lldpEntryProvider(): array
    {
        return [
            'interface name port id' => [
                'entry' => ['lldpRemPortIdSubtype' => 5, 'lldpRemPortId' => 'Gi1/0/1', 'lldpRemPortDesc' => 'uplink', 'lldpRemSysName' => 'core1', 'lldpRemSysDesc' => 'Cisco IOS'],
                'expected' => ['portId' => 'Gi1/0/1', 'portIdSubtype' => LldpPortIdSubtype::InterfaceName, 'portDescr' => 'uplink', 'sysName' => 'core1', 'sysDescr' => 'Cisco IOS', 'portMac' => null],
            ],
            'hex encoded interface name with trailing null' => [
                'entry' => ['lldpRemPortIdSubtype' => 5, 'lldpRemPortId' => '47 69 31 2F 30 2F 31 00 '],
                'expected' => ['portId' => 'Gi1/0/1'],
            ],
            'text is cut at a null byte' => [
                'entry' => ['lldpRemSysName' => "sw35.soada\0\0\0\n00 00 00 00"],
                'expected' => ['sysName' => 'sw35.soada'],
            ],
            'hex encoded text with null padding and garbage' => [
                'entry' => ['lldpRemSysName' => '73 77 33 35 00 00 0A 30 30'],
                'expected' => ['sysName' => 'sw35'],
            ],
            'null padded name printed as dots with garbage' => [
                'entry' => ['lldpRemSysName' => "peca-sw02.......\n00 00 00 00 00 00 00 00"],
                'expected' => ['sysName' => 'peca-sw02'],
            ],
            'multi-line description is kept' => [
                'entry' => ['lldpRemSysDesc' => "Huawei Versatile Routing Platform Software\r\nVRP (R) software"],
                'expected' => ['sysDescr' => "Huawei Versatile Routing Platform Software\r\nVRP (R) software"],
            ],
            'numeric port id is not hex decoded' => [
                'entry' => ['lldpRemPortIdSubtype' => 7, 'lldpRemPortId' => '18'],
                'expected' => ['portId' => '18', 'portIdSubtype' => LldpPortIdSubtype::Local],
            ],
            'printable hex-like port id is not decoded' => [
                'entry' => ['lldpRemPortIdSubtype' => 5, 'lldpRemPortId' => '41 42'],
                'expected' => ['portId' => '41 42'],
            ],
            'mac port id as hex string' => [
                'entry' => ['lldpRemPortIdSubtype' => 3, 'lldpRemPortId' => 'AC A3 1E C3 4B E6 '],
                'expected' => ['portId' => 'aca31ec34be6', 'portMac' => 'aca31ec34be6'],
            ],
            'mac port id with colons' => [
                'entry' => ['lldpRemPortIdSubtype' => 3, 'lldpRemPortId' => '0:1a:2b:3c:4d:5e'],
                'expected' => ['portMac' => '001a2b3c4d5e'],
            ],
            'mac port id printed as raw bytes' => [
                'entry' => ['lldpRemPortIdSubtype' => 3, 'lldpRemPortId' => 'ABCDEF'],
                'expected' => ['portMac' => '414243444546'],
            ],
            'invalid mac port id is kept as text' => [
                'entry' => ['lldpRemPortIdSubtype' => 3, 'lldpRemPortId' => 'not a mac'],
                'expected' => ['portId' => 'not a mac', 'portMac' => null],
            ],
            'chassis interface name is the port name when the port id is a mac (VMware)' => [
                'entry' => ['lldpRemChassisIdSubtype' => 6, 'lldpRemChassisId' => 'vmnic5', 'lldpRemPortIdSubtype' => 3, 'lldpRemPortId' => '00:0a:f7:ec:d1:61', 'lldpRemPortDesc' => 'port 3 on dvSwitch'],
                'expected' => ['portId' => 'vmnic5', 'portIdSubtype' => LldpPortIdSubtype::InterfaceName, 'portMac' => '000af7ecd161', 'chassisMac' => null],
            ],
            'chassis interface alias is the port name when there is no port id' => [
                'entry' => ['lldpRemChassisIdSubtype' => 2, 'lldpRemChassisId' => 'uplink', 'lldpRemPortIdSubtype' => 7, 'lldpRemPortId' => ''],
                'expected' => ['portId' => 'uplink', 'portIdSubtype' => LldpPortIdSubtype::InterfaceAlias],
            ],
            'chassis interface name does not replace a port name' => [
                'entry' => ['lldpRemChassisIdSubtype' => 6, 'lldpRemChassisId' => 'vmnic5', 'lldpRemPortIdSubtype' => 5, 'lldpRemPortId' => 'eth0'],
                'expected' => ['portId' => 'eth0', 'portMac' => null],
            ],
            'network address port id' => [
                'entry' => ['lldpRemPortIdSubtype' => 4, 'lldpRemPortId' => '01 C0 A8 01 02'],
                'expected' => ['portId' => '192.168.1.2', 'portIdSubtype' => LldpPortIdSubtype::NetworkAddress],
            ],
            'unknown port id subtype is local' => [
                'entry' => ['lldpRemPortIdSubtype' => 0, 'lldpRemPortId' => 'port 1'],
                'expected' => ['portIdSubtype' => LldpPortIdSubtype::Local],
            ],
            'chassis mac' => [
                'entry' => ['lldpRemChassisIdSubtype' => 4, 'lldpRemChassisId' => '00 11 22 33 44 55 '],
                'expected' => ['chassisMac' => '001122334455'],
            ],
            'zero chassis mac is ignored' => [
                'entry' => ['lldpRemChassisIdSubtype' => 4, 'lldpRemChassisId' => '00 00 00 00 00 00'],
                'expected' => ['chassisMac' => null],
            ],
            'chassis mac is only used for mac subtype' => [
                'entry' => ['lldpRemChassisIdSubtype' => 7, 'lldpRemChassisId' => '00 11 22 33 44 55 '],
                'expected' => ['chassisMac' => null],
            ],
            'chassis network address is the management ip' => [
                'entry' => ['lldpRemChassisIdSubtype' => 5, 'lldpRemChassisId' => '01 0A 00 00 01'],
                'expected' => ['managementIp' => '10.0.0.1'],
            ],
            'ipv6 chassis network address' => [
                'entry' => ['lldpRemChassisIdSubtype' => 5, 'lldpRemChassisId' => '02 20 01 0D B8 00 00 00 00 00 00 00 00 00 00 00 01'],
                'expected' => ['managementIp' => '2001:db8::1'],
            ],
            'management address is preferred over chassis network address' => [
                'entry' => ['lldpRemChassisIdSubtype' => 5, 'lldpRemChassisId' => '01 0A 00 00 01'],
                'expected' => ['managementIp' => '192.0.2.1'],
                'managementIp' => '192.0.2.1',
            ],
            'whitespace is trimmed' => [
                'entry' => ['lldpRemSysName' => ' switch1 ', 'lldpRemSysDesc' => "line 1\nline 2\n", 'lldpRemPortDesc' => ' Port 1 '],
                'expected' => ['sysName' => 'switch1', 'sysDescr' => "line 1\nline 2", 'portDescr' => 'Port 1'],
            ],
            'missing columns' => [
                'entry' => [],
                'expected' => ['sysName' => '', 'sysDescr' => '', 'portId' => '', 'portDescr' => '', 'managementIp' => null, 'chassisMac' => null, 'platform' => null],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $expected
     */
    #[DataProvider('lldpEntryProvider')]
    public function testFromLldpRemEntry(array $entry, array $expected, ?string $managementIp = null): void
    {
        $neighbor = NeighborParser::fromLldpRemEntry($entry, 42, $managementIp);

        $this->assertSame('lldp', $neighbor->protocol);
        $this->assertSame(42, $neighbor->localPortId);

        foreach ($expected as $property => $value) {
            $this->assertSame($value, $neighbor->$property, "Neighbor $property is incorrect");
        }
    }

    /**
     * @return array<string, array{0: string|null, 1: string|null}>
     */
    public static function macProvider(): array
    {
        return [
            'hex string' => ['AC A3 1E C3 4B E6', 'aca31ec34be6'],
            'hex string with trailing space' => ['AC A3 1E C3 4B E6 ', 'aca31ec34be6'],
            'hex string ending in 00' => ['AC A3 1E C3 4B 00 ', 'aca31ec34b00'],
            'colon delimited' => ['0:1a:2b:3c:4d:5e', '001a2b3c4d5e'],
            'dash delimited' => ['00-1A-2B-3C-4D-5E', '001a2b3c4d5e'],
            'cisco dotted' => ['001A.2B3C.4D5E', '001a2b3c4d5e'],
            'no delimiter' => ['001a2b3c4d5e', '001a2b3c4d5e'],
            'raw bytes' => ['ABCDEF', '414243444546'],
            'all zero mac' => ['00 00 00 00 00 00', null],
            'text' => ['not a mac', null],
            'empty' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('macProvider')]
    public function testParseMac(?string $value, ?string $expected): void
    {
        $this->assertSame($expected, NeighborParser::parseMac($value));
    }
}
