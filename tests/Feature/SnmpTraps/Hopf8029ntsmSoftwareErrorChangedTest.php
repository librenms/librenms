<?php

/**
 * Hopf8029ntsmSoftwareErrorChangedTest.php
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

final class Hopf8029ntsmSoftwareErrorChangedTest extends SnmpTrapTestCase
{
    public function testErrorPresent(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::SoftwareErrorChanged
HOPF8029NTSM-MIB::SyncModuleErrorPCID 1
HOPF8029NTSM-MIB::SyncModuleErrorTimeZone 0
HOPF8029NTSM-MIB::SyncModuleErrorDST 0
HOPF8029NTSM-MIB::SyncModuleErrorSyncProtocol 0
TRAP,
            'SNMP Trap: software error status changed on {{ hostname }} (PCID: 1, timeZone: 0, DST: 0, syncProtocol: 0)',
            'Could not handle testErrorPresent trap',
            [Severity::Warning, 'sensor'],
        );
    }

    public function testNoError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::SoftwareErrorChanged
HOPF8029NTSM-MIB::SyncModuleErrorPCID 0
HOPF8029NTSM-MIB::SyncModuleErrorTimeZone 0
HOPF8029NTSM-MIB::SyncModuleErrorDST 0
HOPF8029NTSM-MIB::SyncModuleErrorSyncProtocol 0
TRAP,
            'SNMP Trap: software error status changed on {{ hostname }} (PCID: 0, timeZone: 0, DST: 0, syncProtocol: 0)',
            'Could not handle testNoError trap',
            [Severity::Ok, 'sensor'],
        );
    }
}
