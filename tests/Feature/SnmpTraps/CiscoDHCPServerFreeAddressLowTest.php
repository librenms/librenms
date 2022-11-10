<?php
/**
 * CiscoDHCPServerFreeAddressLowTest.php
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
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class CiscoDHCPServerFreeAddressLowTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoMacViolation trap handle
     *
     * @return void
     */
    public function testCiscoDHCPServerFreeAddressLow(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (1714266504) 198 days, 9:51:05.04
SNMPv2-MIB::snmpTrapOID.0 = OID: CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerFreeAddressLow
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddrLowThreshold."some-dhcp-pool" = INTEGER: 228
CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddresses."some-dhcp-pool" = INTEGER: 99
TRAP,
            'SNMP Trap: DHCP pool "some-dhcp-pool" address space low. Free addresses: \'99\' addresses.',
            'Could not handle CiscoDHCPServerFreeAddressHigh Test trap',
            [4],
        );
    }
}
