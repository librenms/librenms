<?php
/**
 * CienaCesAAAUserAuthenticationEventTest.php
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
 *
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class CienaCesAAAUserAuthenticationEventTest extends SnmpTrapTestCase
{
    public function testAuthSuccess(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationEvent
CIENA-GLOBAL-MIB::cienaGlobalSeverity info
CIENA-GLOBAL-MIB::cienaGlobalMacAddress ac:89:de:ad:be:ef
CIENA-CES-AAA-MIB::cienaCesAAAUserName lorem
CIENA-CES-AAA-MIB::cienaCesAAAHost 192.168.251.251
CIENA-CES-AAA-MIB::cienaCesAAAUserPort 4444
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationServiceType radius
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationStatus success
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationDescription User authentication succeeded
TRAP,
            'Authentication attempt by lorem. User authentication succeeded',
            'Could not handle CienaCesAAAUserAuthenticationEvent success',
            [Severity::Notice],
        );
    }

    public function testAuthFail(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationEvent
CIENA-GLOBAL-MIB::cienaGlobalSeverity info
CIENA-GLOBAL-MIB::cienaGlobalMacAddress ac:89:de:ad:be:ef
CIENA-CES-AAA-MIB::cienaCesAAAUserName lorem
CIENA-CES-AAA-MIB::cienaCesAAAHostIp 10.1.1.1
CIENA-CES-AAA-MIB::cienaCesAAAUserPort 6564
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationServiceType radius
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationStatus failure
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationDescription User authentication failed
TRAP,
            'Authentication attempt by lorem. User authentication failed',
            'Could not handle CienaCesAAAUserAuthenticationEvent failure',
            [Severity::Warning],
        );
    }

    public function testAuthLogout(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationEvent
CIENA-GLOBAL-MIB::cienaGlobalSeverity info
CIENA-GLOBAL-MIB::cienaGlobalMacAddress ac:89:de:ad:be:ef
CIENA-CES-AAA-MIB::cienaCesAAAUserName lorem
CIENA-CES-AAA-MIB::cienaCesAAAHostIp 10.10.10.10
CIENA-CES-AAA-MIB::cienaCesAAAUserPort 8899
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationServiceType local
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationStatus success
CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationDescription User logout succeeded
TRAP,
            'Authentication attempt by lorem. User logout succeeded',
            'Could not handle CienaCesAAAUserAuthenticationEvent logout',
            [Severity::Notice],
        );
    }
}
