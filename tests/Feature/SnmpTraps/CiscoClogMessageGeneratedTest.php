<?php

/**
 * CiscoClogMessageGeneratedTest.php
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

#[TestDox('Cisco Clog Message Generated Trap')]
final class CiscoClogMessageGeneratedTest extends SnmpTrapTestCase
{
    #[TestDox('Cisco Clog Message Generated - Link Up')]
    public function testCiscoClogMessageGeneratedLinkUp(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.0.0.1]:162]:
DISMAN-EXPRESSION-MIB::sysUpTimeInstance 12 days, 17:06:55.96
SNMPv2-MIB::snmpTrapOID.0 CISCO-SYSLOG-MIB::clogMessageGenerated
CISCO-SYSLOG-MIB::clogHistFacility.19669 LINK
CISCO-SYSLOG-MIB::clogHistSeverity.19669 error
CISCO-SYSLOG-MIB::clogHistMsgName.19669 UPDOWN
CISCO-SYSLOG-MIB::clogHistMsgText.19669 Interface GigabitEthernet1/0/38, changed state to up
CISCO-SYSLOG-MIB::clogHistTimestamp.19669 12 days, 17:06:55.96',
            'Cisco Syslog Trap: LINK UP: Interface GigabitEthernet1/0/38, changed state to up',
            'Could not handle CiscoClogMessageGenerated trap',
            [Severity::Ok],
        );
    }
}
