<?php

/**
 * ApcLowBatteryTest.php
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

final class ApcLowBatteryTest extends SnmpTrapTestCase
{
    /**
     * Test ApcLowBattery handle
     *
     * @return void
     */
    public function testApcLowBattery(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 147:21:00:10.20
SNMPv2-MIB::snmpTrapOID.0 PowerNet-MIB::lowBattery
PowerNet-MIB::mtrapargsString.0 "UPS: The battery power is too low to continue to support the load; the UPS will shut down if input power does not return to normal soon."
SNMPv2-MIB::snmpTrapEnterprise.0 PowerNet-MIB::apc
TRAP,
            'UPS: The battery power is too low to continue to support the load; the UPS will shut down if input power does not return to normal soon.',
            'Could not handle testApcLowBattery trap',
            [Severity::Error],
        );
    }
}
