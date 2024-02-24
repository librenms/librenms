<?php
/**
 * RuckusEventTest.php
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
 * Tests generic Ruckus Wireless event trap handlers.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class RuckusEventTest extends SnmpTrapTestCase
{
    public function testRuckusAssocTrap(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-EVENT-MIB::ruckusEventAssocTrap
RUCKUS-EVENT-MIB::ruckusEventClientMacAddr "de:ad:be:ef:11:221.0.5.1.1.1.2.2"
TRAP,
            'Client de:ad:be:ef:11:22 associated',
            'Could not handle ruckusEventAssocTrap',
        );
    }

    public function testRuckusDiassocTrap(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-EVENT-MIB::ruckusEventDiassocTrap
RUCKUS-EVENT-MIB::ruckusEventClientMacAddr "de:ad:be:ef:33:441.0.5.1.1.1.2.2"
TRAP,
            'Client de:ad:be:ef:33:44 disassociated',
            'Could not handle ruckusEventDiassocTrap',
        );
    }

    public function testRuckusSetErrorTrap(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-EVENT-MIB::ruckusEventSetErrorTrap
RUCKUS-EVENT-MIB::ruckusEventSetErrorOID Wrong Type (should be OBJECT IDENTIFIER): "1.3.6.1.2.1.25.1.1.0.5.1.1.1.2.2"
TRAP,
            'SNMP set error on oid 1.3.6.1.2.1.25.1.1.0.5.1.1.1.2.2',
            'Could not handle ruckusEventSetErrorTrap',
        );
    }
}
