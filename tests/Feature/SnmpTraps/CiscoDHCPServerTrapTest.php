<?php
/**
 * CiscoDHCPServerTrapTest.php
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
 * @copyright  2022 Josh Silvas
 * @author     Josh Silvas <josh@jsilvas.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class CiscoDHCPServerTrapTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoDHCPServer trap handles
     *
     * @return void
     */
    public function testCiscoDHCPServerFreeAddressHigh(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (1714266504) 198 days, 9:51:05.04
SNMPv2-MIB::snmpTrapOID.0 CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerFreeAddressHigh
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddrHighThreshold."some-dhcp-pool" = INTEGER: 228
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddresses."some-dhcp-pool" = INTEGER: 99',
            'SNMP Trap: DHCP pool "some-dhcp-pool" address space high. Free addresses: \'= INTEGER: 99\' addresses.',
            'Could not handle CiscoDHCPServerFreeAddressHigh Test trap',
            [Severity::Info],
        );
    }

    public function testCiscoDHCPServerFreeAddressLow(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (1714275597) 198 days, 9:52:35.97
SNMPv2-MIB::snmpTrapOID.0 CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerFreeAddressLow
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddrLowThreshold."some-dhcp-pool" = INTEGER: 7
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddresses."some-dhcp-pool" = INTEGER: 99',
            'SNMP Trap: DHCP pool "some-dhcp-pool" address space low. Free addresses: \'= INTEGER: 99\' addresses.',
            'Could not handle CiscoDHCPServerFreeAddressHigh Test trap',
            [Severity::Error],
        );
    }

    public function testCiscoDHCPServerStart(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:51988->[10.0.0.1]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (45460476) 5 days, 6:16:44.76
SNMPv2-MIB::snmpTrapOID.0 CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStartTime
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStartTime = Hex-STRING: 07 E6 0B 0A 03 0F 25 00 2B 00',
            'SNMP Trap: Device DHCP service started.',
            'Could not handle CiscoDHCPServerStart Test trap',
            [Severity::Info],
        );
    }

    public function testCiscoDHCPServerStop(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:51988->[10.0.0.1]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (45460476) 5 days, 6:16:44.76
SNMPv2-MIB::snmpTrapOID.0 CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStopTime
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStopTime = Hex-STRING: 07 E6 0B 0A 03 0F 25 00 2B 00',
            'SNMP Trap: Device DHCP service stopped.',
            'Could not handle CiscoDHCPServerStop Test trap',
            [Severity::Error],
        );
    }
}
