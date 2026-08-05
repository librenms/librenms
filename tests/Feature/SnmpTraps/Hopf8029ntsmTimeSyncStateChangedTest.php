<?php

/**
 * Hopf8029ntsmTimeSyncStateChangedTest.php
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

final class Hopf8029ntsmTimeSyncStateChangedTest extends SnmpTrapTestCase
{
    public function testRadioSyncHighPrecision(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::TimeSyncStateChanged
HOPF8029NTSM-MIB::SyncModuleTimeSyncState "R"
TRAP,
            'SNMP Trap: sync module time sync state changed to R',
            'Could not handle testRadioSyncHighPrecision trap',
            [Severity::Ok, 'sensor'],
        );
    }

    public function testInvalidTimeAndDate(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::TimeSyncStateChanged
HOPF8029NTSM-MIB::SyncModuleTimeSyncState "I"
TRAP,
            'SNMP Trap: sync module time sync state changed to I',
            'Could not handle testInvalidTimeAndDate trap',
            [Severity::Error, 'sensor'],
        );
    }

    public function testCrystalMode(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::TimeSyncStateChanged
HOPF8029NTSM-MIB::SyncModuleTimeSyncState "C"
TRAP,
            'SNMP Trap: sync module time sync state changed to C',
            'Could not handle testCrystalMode trap',
            [Severity::Warning, 'sensor'],
        );
    }
}
