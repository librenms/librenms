<?php
/**
 * OspfTxRetransmitTest.php
 *
 * -Description-
 *
 * Unit test for the OspfTxRetransmit SNMP trap handler. Will verify
 * that the trap is handled correctly logging the right event.
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

class OspfTxRetransmitTest extends SnmpTrapTestCase
{
    /**
     * Test OSPF lsUpdate packet type trap
     *
     * @return void
     */
    public function testLsUpdatePacket(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 16:21:49.33
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfTxRetransmit
OSPF-MIB::ospfRouterId 10.1.2.3
OSPF-MIB::ospfIfIpAddress 10.8.9.10
OSPF-MIB::ospfAddressLessIf 0
OSPF-MIB::ospfNbrRtrId 10.3.4.5
OSPF-TRAP-MIB::ospfPacketType lsUpdate
OSPF-MIB::ospfLsdbType routerLink
OSPF-MIB::ospfLsdbLsid 10.1.1.0
OSPF-MIB::ospfLsdbRouterId 10.4.5.6
TRAP,
            'SNMP Trap: OSPFTxRetransmit trap received from {{ hostname }}(Router ID: 10.1.2.3). A lsUpdate packet was sent to 10.3.4.5. LSType: routerLink, route ID: 10.1.1.0, originating from 10.4.5.6.',
            'Could not handle testlsUpdatePacket trap',
        );
    }

    /**
     * Test OSPF non lsUpdate packet type
     *
     * @return void
     */
    public function testNotLsUpdatePacket(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 16:21:49.33
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfTxRetransmit
OSPF-MIB::ospfRouterId 10.1.2.3
OSPF-MIB::ospfIfIpAddress 10.8.9.10
OSPF-MIB::ospfAddressLessIf 0
OSPF-MIB::ospfNbrRtrId 10.3.4.5
OSPF-TRAP-MIB::ospfPacketType hello
OSPF-MIB::ospfLsdbType routerLink
OSPF-MIB::ospfLsdbLsid 10.1.1.0
OSPF-MIB::ospfLsdbRouterId 10.4.5.6
TRAP,
            'SNMP TRAP: {{ hostname }}(Router ID: 10.1.2.3) sent a hello packet to 10.3.4.5.',
            'Could not handle testNotLsUpdatePacket trap',
        );
    }
}
