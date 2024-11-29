<?php
/**
 * NetgearFailedUserLoginTest.php
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

class NetgearFailedUserLoginTest extends SnmpTrapTestCase
{
    public function testManagedSeries(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:6:11:31.55
SNMPv2-MIB::snmpTrapOID.0 NETGEAR-SWITCHING-MIB::failedUserLoginTrap
TRAP,
            'SNMP Trap: Failed User Login: {{ hostname }}',
            'Could not handle NETGEAR-SWITCHING-MIB::failedUserLoginTrap trap',
            [Severity::Warning, 'auth'],
        );
    }

    public function testSmartSeries(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:1026->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 30:22:57:58.00
SNMPv2-MIB::snmpTrapOID.0 NETGEAR-SMART-SWITCHING-MIB::failedUserLoginTrap
TRAP,
            'SNMP Trap: Failed User Login: {{ hostname }}',
            'Could not handle NETGEAR-SMART-SWITCHING-MIB::failedUserLoginTrap trap',
            [Severity::Warning, 'auth'],
        );
    }
}
