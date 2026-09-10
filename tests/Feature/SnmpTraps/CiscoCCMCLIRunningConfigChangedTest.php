<?php

/**
 * CiscoCCMCLIRunningConfigChangedTest.php
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

#[TestDox('Cisco CCM CLI Running Config Changed Trap')]
final class CiscoCCMCLIRunningConfigChangedTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoCCMCLIRunningConfigChanged trap handler
     *
     * @return void
     */
    #[TestDox('Cisco CCM CLI Running Config Changed')]
    public function testCiscoCCMCLIRunningConfigChanged(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
DISMAN-EXPRESSION-MIB::sysUpTimeInstance = Timeticks: (498047044) 57 days, 15:27:50.44
SNMPv2-MIB::snmpTrapOID.0 CISCO-CONFIG-MAN-MIB::ccmCLIRunningConfigChanged
CISCO-CONFIG-MAN-MIB::ccmHistoryRunningLastChanged.0 = Timeticks: (498046440) 57 days, 15:27:44.40
CISCO-CONFIG-MAN-MIB::ccmHistoryEventTerminalType.479 = INTEGER: notApplicable(1)',
            'The running config was changed at system uptime = Timeticks: (498046440) 57 days, 15:27:44.40 from terminal type = INTEGER: notApplicable(1)',
            'Could not handle CiscoCCMCLIRunningConfigChanged trap',
            [Severity::Info],
        );
    }
}
