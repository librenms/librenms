<?php
/**
 * SnTrapUserAuthTest.php
 *
 * -Description-
 *
 * Tests handling of the SNMPTraps for user login/logout.
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
 * @copyright  2022 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class SnTrapUserAuthTest extends SnmpTrapTestCase
{
    /**
     * Create snTrapUserLogin trap object
     * Test SnTrapUserLogin handler
     *
     * @return void
     */
    public function testSnTrapUserLogin(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-TRAP-MIB::snTrapUserLogin
DISMAN-EVENT-MIB::sysUpTimeInstance 172:9:43:55.64
FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage.0 "Security: ssh login by rancid from src IP {{ ip }} to PRIVILEGED EXEC mode using RSA as Server Host Key. "
TRAP,
            'Security: ssh login by rancid from src IP {{ ip }} to PRIVILEGED EXEC mode using RSA as Server Host Key. ',
            'Could not handle snTrapUserLogin',
        );
    }

    /**
     * Create snTrapUserLogout trap object
     * Test SnTrapUserLogout handler
     *
     * @return void
     */
    public function testSnTrapUserLogout(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-TRAP-MIB::snTrapUserLogin
DISMAN-EVENT-MIB::sysUpTimeInstance 172:9:43:55.64
FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage.0 "Security: ssh logout by rancid from src IP {{ ip }} from USER EXEC mode using RSA as Server Host Key. "
TRAP,
            'Security: ssh logout by rancid from src IP {{ ip }} from USER EXEC mode using RSA as Server Host Key. ',
            'Could not handle snTrapUserLogout',
        );
    }
}
