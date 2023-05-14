<?php
/**
 * ApcOnBatteryTest.php
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
 * @author     Andy Norwood(bonzo81)
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class SophosNotificationTest extends SnmpTrapTestCase
{
    /**
     * Test sfosNotification handle
     *
     * @return void
     */
    public function testsfosNotification(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.1.26]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 SFOS-FIREWALL-MIB::sfosNotification
SFOS-FIREWALL-MIB::sfosDeviceName.0 "FW01"
SFOS-FIREWALL-MIB::sfosTrapMessage.0 "Test Message."
TRAP,
            'This is the test message working',
            'Could not handle testsfosNotification trap',
            [4],
        );
    }
}
