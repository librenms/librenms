<?php
/**
 * AdvaObjectDeletionTest.php
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

class AdvaObjectDeletionTest extends SnmpTrapTestCase
{
    public function testUserDeletion()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
CM-SECURITY-MIB::cmSecurityUserName.\"testuser\".false testuser
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 08 38 1B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.92 92
ADVA-MIB::neEventLogTimeStamp.92 2018-12-10,8:56:27.5,-6:0";

        $trap = new Trap($trapText);

        $message = 'User object testuser deleted';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectDeletionTrap user deletion');
    }

    public function testFLowDeletion()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
CM-FACILITY-MIB::cmFlowIndex.1.1.1.4.1 1
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 07 1C 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.148 148
ADVA-MIB::neEventLogTimeStamp.148 2018-12-10,9:7:28.1,-6:0";

        $trap = new Trap($trapText);

        $message = 'Flow 1-1-1-4-1 deleted';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectDeletionTrap flow deletion');
    }

    public function testLagPortDeletion()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
F3-LAG-MIB::f3LagPortIndex.1.1.1 1
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 03 33 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.136 136
ADVA-MIB::neEventLogTimeStamp.136 2018-12-10,9:3:51.3,-6:0";

        $trap = new Trap($trapText);

        $message = 'LAG member port 1 removed from LAG 1-1';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectDeletionTrap LAG port deletion');
    }

    public function testLagDeletion()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
F3-LAG-MIB::f3LagIndex.1.1 1
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 03 33 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.139 139
ADVA-MIB::neEventLogTimeStamp.139 2018-12-10,9:3:51.4,-6:0";

        $trap = new Trap($trapText);

        $message = 'LAG 1 deleted';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmObjectDeletionTrap LAG deletion');
    }
}
