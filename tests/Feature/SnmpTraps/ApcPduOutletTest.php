<?php
/**
 * ApcPduOutletTest.php
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

class ApcPduOutletTest extends SnmpTrapTestCase
{
    public function testOutletOff(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 84:21:45:07.07
SNMPv2-MIB::snmpTrapOID.0 PowerNet-MIB::outletOff
PowerNet-MIB::mtrapargsInteger.0 2
PowerNet-MIB::mtrapargsString.0 \"An outlet has turned on. If the outlet number is 0, then all outlets have turned on.\"
SNMPv2-MIB::snmpTrapEnterprise.0 PowerNet-MIB::apc
TRAP,
            'APC PDU: Outlet has turned off: 2',
            'Could not handle outletOff trap',
            [Severity::Warning],
        );
    }

    public function testOutletOn(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 84:21:45:07.07
SNMPv2-MIB::snmpTrapOID.0 PowerNet-MIB::outletOn
PowerNet-MIB::mtrapargsInteger.0 2
PowerNet-MIB::mtrapargsString.0 \"An outlet has turned on. If the outlet number is 0, then all outlets have turned on.\"
SNMPv2-MIB::snmpTrapEnterprise.0 PowerNet-MIB::apc
TRAP,
            'APC PDU: Outlet has been turned on: 2',
            'Could not handle outletOn trap',
            [Severity::Warning],
        );
    }

    public function testOutletReboot(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 84:21:45:07.07
SNMPv2-MIB::snmpTrapOID.0 PowerNet-MIB::outletReboot
PowerNet-MIB::mtrapargsInteger.0 2
PowerNet-MIB::mtrapargsString.0 \"An outlet has rebooted. If the outlet number is 0, then all outlets have rebooted.\"
SNMPv2-MIB::snmpTrapEnterprise.0 PowerNet-MIB::apc
TRAP,
            'APC PDU: Outlet has rebooted: 2',
            'Could not handle outletReboot trap',
            [Severity::Warning],
        );
    }
}
