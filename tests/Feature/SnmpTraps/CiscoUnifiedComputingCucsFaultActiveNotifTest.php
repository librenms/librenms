<?php

/**
 * CiscoUnifiedComputingCucsFaultActiveNotifTest.php
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

#[TestDox('Cisco Unified Computing Cucs Fault Active Notif Trap')]
final class CiscoUnifiedComputingCucsFaultActiveNotifTest extends SnmpTrapTestCase
{
    #[TestDox('Cisco Unified Computing Cucs Fault Active Notif')]
    public function testCiscoUnifiedComputingCucsFaultActiveNotif(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
DISMAN-EXPRESSION-MIB::sysUpTimeInstance 191 days, 19:10:52.21
SNMPv2-MIB::snmpTrapOID.0 CISCO-UNIFIED-COMPUTING-MIB::cucsFaultActiveNotif
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultIndex.3161626 3161626
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultDescription.3161626 Virtual interface 702 link state is down
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultAffectedObjectId.3161626 CISCO-UNIFIED-COMPUTING-MIB::ciscoUnifiedComputingMIBObjects.10.3.1.2.250985
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultAffectedObjectDn.3161626 sys/rack-unit-2/adaptor-1/host-eth-2/vif-702
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultCreationTime.3161626 2025-12-11,16:27:4.91
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultLastModificationTime.3161626 2025-12-11,16:27:4.91
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultCode.3161626 fltDcxVIfLinkState
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultType.3161626 10
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultProbableCause.3161626 249
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultSeverity.3161626 major
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultOccur.3161626 1
CISCO-UNIFIED-COMPUTING-MIB::cucsFaultEntry.13.3161626 3161625',
            'Cisco Unified Computing Fault 3161626 Active: Virtual interface 702 link state is down for sys/rack-unit-2/adaptor-1/host-eth-2/vif-702 started at 2025-12-11,16:27:4.91, last updated at 2025-12-11,16:27:4.91. Probable cause: 249',
            'Could not handle CiscoUnifiedComputingCucsFaultActiveNotif trap',
            [Severity::Error],
        );
    }
}
