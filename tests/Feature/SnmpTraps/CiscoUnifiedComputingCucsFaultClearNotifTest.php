<?php

/**
 * CiscoUnifiedComputingCucsFaultClearNotifTest.php
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
 * @copyright  2025 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Cisco Unified Computing Cucs Fault Clear Notif Trap')]
final class CiscoUnifiedComputingCucsFaultClearNotifTest extends SnmpTrapTestCase
{
    #[TestDox('Cisco Unified Computing Cucs Fault Clear Notif')]
    public function testCiscoUnifiedComputingCucsFaultClearNotif(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
DISMAN-EXPRESSION-MIB::sysUpTimeInstance 191 days, 19:13:05.46
SNMPv2-MIB::snmpTrapOID.0 CISCO-UNIFIED-COMPUTING-MIB::cucsFaultClearNotif
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultIndex.3161701 3161701
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultDescription.3161701 lan Member 1/50 of Port-Channel 21 on fabric interconnect A is down, membership: down
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultAffectedObjectId.3161701 CISCO-UNIFIED-COMPUTING-MIB::ciscoUnifiedComputingMIBObjects.19.15.1.2.645950
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultAffectedObjectDn.3161701 fabric/lan/A/pc-21/ep-slot-1-port-50
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultCreationTime.3161701 2025-12-11,16:27:18.36
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultLastModificationTime.3161701 2025-12-11,16:29:18.36
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultCode.3161701 727
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultType.3161701 environmental
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultProbableCause.3161701 applyFailed
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultSeverity.3161701 cleared
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultOccur.3161701 1
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultEntry.13.3161701 3161700',
            'Cisco Unified Computing Fault 3161701 Cleared: lan Member 1/50 of Port-Channel 21 on fabric interconnect A is down, membership: down for fabric/lan/A/pc-21/ep-slot-1-port-50 started at 2025-12-11,16:27:18.36, last updated at 2025-12-11,16:29:18.36. Probable cause: applyFailed',
            'Could not handle CiscoUnifiedComputingCucsFaultClearNotif trap',
            [Severity::Ok],
        );
    }
}
