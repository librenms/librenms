<?php
/**
 * CiscoDHCPServerStopTest.php
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

class CiscoDHCPServerStopTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoMacViolation trap handle
     *
     * @return void
     */
    public function testCiscoDHCPServerStop(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
[UDP: [{{ ip }}]:51988->[10.0.0.1]:162]:
   SNMPv2-MIB::sysUpTime.0 = Timeticks: (45460476) 5 days, 6:16:44.76
   SNMPv2-MIB::snmpTrapOID.0 = OID: CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStopTime
   CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerStopTime = Hex-STRING: 07 E6 0B 0A 03 0F 25 00 2B 00
TRAP,
            'SNMP Trap: Device DHCP service stopped.',
            'Could not handle CiscoDHCPServerStop Test trap',
            [4],
        );
    }
}
