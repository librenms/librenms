<?php

/*
 * FgTrapDhcpTest.php
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
 * Unit tests for Fortigate DHCP SNMP trap handler (FgTrapDhcp)
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class FgTrapDhcpTest extends SnmpTrapTestCase
{
    private function dhcpTrap(string $type, string $message): string
    {
        return "{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 455:10:59:53.53
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapDhcp
FORTINET-CORE-MIB::fnSysSerial.0 FGT60F0000000000
SNMPv2-MIB::sysName.0 {{ hostname }}
IF-MIB::ifName.0 internal5
FORTINET-FORTIGATE-MIB::fgVdEntName.0 root
FORTINET-FORTIGATE-MIB::fgDhcpServerId.0 5
FORTINET-FORTIGATE-MIB::fgDhcpTrapType.0 $type
FORTINET-FORTIGATE-MIB::fgDhcpTrapMessage.0 \"$message\"";
    }

    public function testConflictIp(): void
    {
        $this->assertTrapLogsMessage(
            $this->dhcpTrap('conflictIP', 'IP address 10.0.0.121 is abandoned due to conflict.'),
            'DHCP IP conflict on internal5: IP address 10.0.0.121 is abandoned due to conflict.',
            'Could not handle fgTrapDhcp conflictIP trap',
            [Severity::Warning],
        );
    }

    public function testRunOutOfIpPoolNumeric(): void
    {
        $this->assertTrapLogsMessage(
            $this->dhcpTrap('1', 'DHCP server has run out of IP addresses.'),
            'DHCP pool exhausted on internal5: DHCP server has run out of IP addresses.',
            'Could not handle fgTrapDhcp runOutOfIPPool trap',
            [Severity::Error],
        );
    }

    public function testReceivedNakWithIndex(): void
    {
        $this->assertTrapLogsMessage(
            $this->dhcpTrap('receivedNAK(3)', 'DHCP client received NAK.'),
            'DHCP NAK received on internal5: DHCP client received NAK.',
            'Could not handle fgTrapDhcp receivedNAK trap',
            [Severity::Notice],
        );
    }
}
