<?php
/**
 * AdvaObjectCreationTest.php
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
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class AdvaObjectCreationTest extends SnmpTrapTestCase
{
    public function testUserCreation()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectCreationTrap
CM-SECURITY-MIB::cmSecurityUserPrivLevel.\"testuser\".false superuser
CM-SECURITY-MIB::cmSecurityUserLoginTimeout.\"testuser\".false 15
CM-SECURITY-MIB::cmSecurityUserName.\"testuser\".false testuser
CM-SECURITY-MIB::cmSecurityUserComment.\"testuser\".false Remote User
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 08 37 29 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.91 91
ADVA-MIB::neEventLogTimeStamp.91 2018-12-10,8:55:41.1,-6:0";

        $trap = new Trap($trapText);

        $message = 'User object testuser created';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectCreationTrap user created');
    }

    public function testLagCreation()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectCreationTrap
IEEE8023-LAG-MIB::dot3adAggCollectorMaxDelay.9 50
IEEE8023-LAG-MIB::dot3adAggActorSystemPriority.9 32768
IEEE8023-LAG-MIB::dot3adAggActorAdminKey.9 32768
F3-LAG-MIB::f3LagProtocols.1.1 true
F3-LAG-MIB::f3LagDiscardWrongConversation.1.1 false
F3-LAG-MIB::f3LagFrameDistAlgorithm.1.1 activeStandby
F3-LAG-MIB::f3LagMode.1.1 active-standby
F3-LAG-MIB::f3LagLacpControl.1.1 true
F3-LAG-MIB::f3LagCcmDefectsDetectionEnabled.1.1 false
F3-LAG-MIB::f3LagName.1.1 
F3-LAG-MIB::f3LagEntry.14.1.1 \"B0 00 \"
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 08 3A 2B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.110 110
ADVA-MIB::neEventLogTimeStamp.110 2018-12-10,8:58:43.7,-6:0";

        $trap = new Trap($trapText);

        $message = 'LAG 1 created';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectCreationTrap LAG created');
    }
}
