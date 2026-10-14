<?php

/**
 * SnTrapStackingTest.php
 *
 * -Description-
 *
 * Tests handling of the FastIron stacking traps.
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
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class SnTrapStackingTest extends SnmpTrapTestCase
{
    public function testUnitDeleted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP_WRAP'
        {{ hostname }}
        UDP: [{{ ip }}]:162->[192.168.5.5]:162
        SNMPv2-MIB::sysUpTime.0 1:8:15:23.10
        SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingUnitDeleted
        FOUNDRY-SN-AGENT-MIB::snChasUnitIndex 6
        FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage "Stack: Stack unit 6 has been deleted from the stack system
        "
        TRAP_WRAP,
            'Stack: Stack unit 6 has been deleted from the stack system',
            'Could not handle snTrapStackingUnitDeleted',
            [Severity::Warning],
        );
    }

    public function testUnitAdded(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP_WRAP'
        {{ hostname }}
        UDP: [{{ ip }}]:162->[192.168.5.5]:162
        SNMPv2-MIB::sysUpTime.0 1:8:16:05.30
        SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingUnitAdded
        FOUNDRY-SN-AGENT-MIB::snChasUnitIndex 2
        FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage "Stack: Stack unit 2 has been added to the stack system
        "
        TRAP_WRAP,
            'Stack: Stack unit 2 has been added to the stack system',
            'Could not handle snTrapStackingUnitAdded',
            [Severity::Notice],
        );
    }

    public function testMasterElected(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP_WRAP'
        {{ hostname }}
        UDP: [{{ ip }}]:162->[192.168.5.5]:162
        SNMPv2-MIB::sysUpTime.0 1:8:15:46.30
        SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingMasterElected
        FOUNDRY-SN-AGENT-MIB::snChasUnitIndex 3
        FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage "Stack: Stack unit 3 has been elected as ACTIVE unit of the stack system
        "
        TRAP_WRAP,
            'Stack: Stack unit 3 has been elected as ACTIVE unit of the stack system',
            'Could not handle snTrapStackingMasterElected',
            [Severity::Warning],
        );
    }

    public function testPowerSupplyFailedWithoutMessage(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP_WRAP'
        {{ hostname }}
        UDP: [{{ ip }}]:162->[192.168.5.5]:162
        SNMPv2-MIB::sysUpTime.0 1:8:20:00.00
        SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingChasPwrSupplyFailed
        FOUNDRY-SN-AGENT-MIB::snChasUnitIndex 4
        FOUNDRY-SN-AGENT-MIB::snChasPwrSupplyIndex 1
        TRAP_WRAP,
            'FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingChasPwrSupplyFailed for stack unit 4',
            'Could not handle snTrapStackingChasPwrSupplyFailed',
            [Severity::Error],
        );
    }
}
