<?php

/**
 * CiscoDHCPServerTrapTest.php
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
 * @copyright  2026 Neil Lathwood
 * @author     Neil Lathwood <neil@configuration.co.uk>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Cisco Config Man Event Trap')]
final class CiscoConfigManEventTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoConfigManEvent trap handles
     *
     * @return void
     */
    #[TestDox('Cisco Config Man Event')]
    public function testCiscoConfigManEvent(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
DISMAN-EXPRESSION-MIB::sysUpTimeInstance = Timeticks: (463147133) 53 days, 14:31:11.33
SNMPv2-MIB::snmpTrapOID.0 CISCO-CONFIG-MAN-MIB::ciscoConfigManEvent
CISCO-CONFIG-MAN-MIB::ccmHistoryEventCommandSource.963 = INTEGER: commandLine(1)
CISCO-CONFIG-MAN-MIB::ccmHistoryEventConfigSource.963 = INTEGER: running(3)
CISCO-CONFIG-MAN-MIB::ccmHistoryEventConfigDestination.963 = INTEGER: commandSource(2)',
            'A configuration management event was triggered via = INTEGER: commandLine(1) from config source = INTEGER: running(3) to config destination = INTEGER: commandSource(2)',
            'Could not handle CiscoConfigManEvent Test trap',
            [Severity::Info],
        );
    }
}
