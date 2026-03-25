<?php

/**
 * ApcDischargeClearedTest.php
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

final class ApcDischargeClearedTest extends SnmpTrapTestCase
{
    /**
     * Test ApcDischargeCleared handle
     *
     * @return void
     */
    public function testApcDischargeCleared(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
SNMPv2-MIB::sysUpTime.0 0:0:44:22.50
SNMPv2-MIB::snmpTrapOID.0 PowerNet-MIB::dischargeCleared
PowerNet-MIB::mtrapargsString "UPS: A discharged battery condition no longer exists."
SNMPv2-MIB::snmpTrapEnterprise.0 PowerNet-MIB::apc
TRAP,
            'UPS: A discharged battery condition no longer exists.',
            'Could not handle testApcDischargeCleared trap',
            [Severity::Ok],
        );
    }
}
