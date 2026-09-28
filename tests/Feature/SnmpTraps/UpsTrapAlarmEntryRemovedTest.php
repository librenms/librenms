<?php

/**
 * UpsTrapAlarmEntryRemovedTest.php
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

final class UpsTrapAlarmEntryRemovedTest extends SnmpTrapTestCase
{
    /**
     * Test UpsTrapAlarmEntryRemoved handle for an untranslated OID, as seen from
     * some APC NMC firmware that encodes the trap OID as enterprise.0.specific.
     *
     * @return void
     */
    public function testUpsTrapAlarmEntryRemovedUntranslated(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 47:22:17:01.20
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-SMI::mib-2.33.2.0.4
SNMPv2-SMI::mib-2.33.1.6.2.1.1.0 16
SNMPv2-SMI::mib-2.33.1.6.2.1.2.0 SNMPv2-SMI::mib-2.33.1.6.3.20
SNMPv2-SMI::snmpModules.18.1.3.0 10.0.0.1
SNMPv2-SMI::snmpModules.18.1.4.0 public
SNMPv2-MIB::snmpTrapEnterprise.0 SNMPv2-SMI::mib-2.33.2
TRAP,
            'upsAlarmCommunicationsLost: A problem has been encountered in the communications between the agent and the UPS. cleared',
            'Could not handle testUpsTrapAlarmEntryRemovedUntranslated trap',
            [Severity::Ok],
        );
    }
}
