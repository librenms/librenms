<?php

/**
 * Hopf8029ntsmUpdateInitTest.php
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

final class Hopf8029ntsmUpdateInitTest extends SnmpTrapTestCase
{
    public function testUpdateInit(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 HOPF8029NTSM-MIB::UpdateInit
HOPF8029NTSM-MIB::hopf8029NTSMFirmwareVersion "01.02"
HOPF8029NTSM-MIB::hopf8029NTSMFirmwareDate 2024-1-1,0:0:0.0
TRAP,
            'SNMP Trap: firmware update initiated on {{ hostname }} (previous version: 01.02, dated 2024-1-1,0:0:0.0)',
            'Could not handle testUpdateInit trap',
            [Severity::Warning, 'reboot'],
        );
    }
}
