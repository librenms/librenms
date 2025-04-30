<?php

/**
 * RuckusSzApRadiusServerReachabilityTrapTest.php
 *
 * -Description-
 *
 * Test Ruckus RADIUS reachability traps.
 * Tests RuckusSzApRadiusServerUnreachableTrap and
 * RuckusSzApRadiusServerReachableTrap.
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
 * @copyright  2025 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class RuckusSzApRadiusServerReachabilityTrapTest extends SnmpTrapTestCase
{
    public function testRadiusUnreachableIpv4()
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:8:26:09.00
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPRadiusServerUnreachableTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 Major
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 2102
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 radiusServerUnreachable
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 test-ap-720
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 18:4B:DE:AD:BE:EF
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 192.168.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 somewhere
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 someplace
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPGPSCoordinates.0 0.0000 0.0000
RUCKUS-SZ-EVENT-MIB::ruckusSZRadSrvrIp.0 10.0.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIPv6.0 ""
TRAP,
            'AP test-ap-720 (192.168.0.1) is unable to reach radius server 10.0.0.1',
            'Could not handle RuckusSzApRadiusServerUnreachableTrapTest IPv4 only.',
            [Severity::Warning],
        );
    }

    public function testRadiusUnreachableIpBoth()
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:8:26:09.00
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPRadiusServerUnreachableTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 Major
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 2102
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 radiusServerUnreachable
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 test-ap-720
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 18:4B:DE:AD:BE:EF
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 192.168.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 somewhere
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 someplace
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPGPSCoordinates.0 0.0000 0.0000
RUCKUS-SZ-EVENT-MIB::ruckusSZRadSrvrIp.0 10.0.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIPv6.0 2001:db8::dead:beef
TRAP,
            'AP test-ap-720 (192.168.0.1, 2001:db8::dead:beef) is unable to reach radius server 10.0.0.1',
            'Could not handle RuckusSzApRadiusServerUnreachableTrapTest IPv4 and IPv6.',
            [Severity::Warning],
        );
    }

    public function testRadiusReachableIpv4()
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:8:26:54.00
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPRadiusServerReachableTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 Informational
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 2101
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 radiusServerReachable
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 test-ap-22
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 18:4B:DE:AD:BE:EF
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 192.168.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 somewhere
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 someplace
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPGPSCoordinates.0 180.0000 180.000
RUCKUS-SZ-EVENT-MIB::ruckusSZRadSrvrIp.0 10.0.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIPv6.0 ""
TRAP,
            'AP test-ap-22 (192.168.0.1) is able to reach radius server 10.0.0.1',
            'Could not handle RuckusSZAPRadiusServerReachableTrap IPv4 only.',
            [Severity::Ok],
        );
    }

    public function testRadiusReachableIpBoth()
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:8:26:54.00
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPRadiusServerReachableTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 Informational
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 2101
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 radiusServerReachable
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 test-ap-22
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 18:4B:DE:AD:BE:EF
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 192.168.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 somewhere
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 someplace
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPGPSCoordinates.0 180.0000 180.000
RUCKUS-SZ-EVENT-MIB::ruckusSZRadSrvrIp.0 10.0.0.1
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIPv6.0 2001:db8::dead:beef
TRAP,
            'AP test-ap-22 (192.168.0.1, 2001:db8::dead:beef) is able to reach radius server 10.0.0.1',
            'Could not handle RuckusSZAPRadiusServerReachableTrap IPv4 and IPv6.',
            [Severity::Ok],
        );
    }
}
