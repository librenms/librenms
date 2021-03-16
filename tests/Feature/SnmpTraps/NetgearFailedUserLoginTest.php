<?php
/**
 * NetgearFailedUserLoginTest.php
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

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class NetgearFailedUserLoginTest extends SnmpTrapTestCase
{
    public function testManagedSeries()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:6:11:31.55
SNMPv2-MIB::snmpTrapOID.0 NETGEAR-SWITCHING-MIB::failedUserLoginTrap";

        $message = "SNMP Trap: Failed User Login: {$device->displayName()}";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'auth', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle NETGEAR-SWITCHING-MIB::failedUserLoginTrap trap');
    }

    public function testSmartSeries()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:1026->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 30:22:57:58.00
SNMPv2-MIB::snmpTrapOID.0 NETGEAR-SMART-SWITCHING-MIB::failedUserLoginTrap";

        $message = "SNMP Trap: Failed User Login: {$device->displayName()}";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'auth', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle NETGEAR-SMART-SWITCHING-MIB::failedUserLoginTrap trap');
    }
}
