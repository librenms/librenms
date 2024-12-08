<?php
/**
 * RuckusSzClusterStateTest.php
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
 * Tests Ruckus Wireless SmartZone cluster state trap handlers..
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class RuckusSzClusterStateTest extends SnmpTrapTestCase
{
    public function testClusterInMaintenance(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZClusterInMaintenanceStateTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 "Critical"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 "807"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 "clusterInMaintenanceState"
RUCKUS-SZ-EVENT-MIB::ruckusSZClusterName.0 "{{ hostname }}"
TRAP,
            'Smartzone cluster {{ hostname }} state changed to maintenance',
            'Could not handle ruckusSZClusterInMaintenanceStateTrap',
            [Severity::Notice],
        );
    }

    public function testClusterInService(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZClusterBackToInServiceTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 "Informational"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 "808"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 "clusterBackToInService"
RUCKUS-SZ-EVENT-MIB::ruckusSZClusterName.0 "{{ hostname }}"
TRAP,
            'Smartzone cluster {{ hostname }} is now in service',
            'Could not handle ruckusSZClusterBackToInServiceTrap',
        );
    }
}
