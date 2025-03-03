<?php
/**
 * CiscoPortSecurity.php
 *
 * -Description-
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

use LibreNMS\Enum\Severity;

class CiscoMacViolationTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoMacViolation trap handle
     *
     * @return void
     */
    public function testMacViolation(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 CISCO-PORT-SECURITY-MIB::cpsSecureMacAddrViolation
IF-MIB::ifIndex 10104
IF-MIB::ifName GigabitEthernet1/0/24
CISCO-PORT-SECURITY-MIB::cpsIfSecureLastMacAddress a8:9d:21:e1:d8:50
TRAP,
            'SNMP Trap: Secure MAC Address Violation on port GigabitEthernet1/0/24. Last MAC address: a8:9d:21:e1:d8:50',
            'Could not handle testMacViolation trap',
            [Severity::Warning],
        );
    }
}
