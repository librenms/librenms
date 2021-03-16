<?php
/**
 * JnxDomLaneAlarmTest.php
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
 *
 * Tests JnxDomAlertSet and JnxDomAlertCleared traps from Juniper devices.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class JnxDomLaneAlarmTest extends SnmpTrapTestCase
{
    public function testJnxDomLaneAlarmSetTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomLaneAlarmSet
IF-MIB::ifDescr.$port->ifIndex $port->ifDescr
JUNIPER-DOM-MIB::jnxDomLaneIndex.$port->ifIndex 0
JUNIPER-DOM-MIB::jnxDomLaneLastAlarms.$port->ifIndex \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentLaneAlarms.$port->ifIndex \"40 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentLaneAlarmDate.$port->ifIndex 2019-4-10,0:9:35.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX960";

        \Log::shouldReceive('warning')->never()->with("Snmptrap JnxDomLaneAlarmSet: Could not find port at ifIndex $port->ifIndex for device: $device->hostname");

        $trap = new Trap($trapText);
        $message = "DOM lane alarm on interface $port->ifDescr lane 0. Current alarm(s): input signal low";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomLaneAlarmSet');
    }

    public function testJnxDomLaneAlarmClearedTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomLaneAlarmCleared
IF-MIB::ifDescr.$port->ifIndex $port->ifDescr
JUNIPER-DOM-MIB::jnxDomLaneIndex.$port->ifIndex 0
JUNIPER-DOM-MIB::jnxDomLaneLastAlarms.$port->ifIndex \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentLaneAlarms.$port->ifIndex \"08 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentLaneAlarmDate.$port->ifIndex 2019-4-10,0:9:35.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX960";

        \Log::shouldReceive('warning')->never()->with("Snmptrap JnxDomLaneAlarmCleared: Could not find port at ifIndex $port->ifIndex for device: $device->hostname");

        $trap = new Trap($trapText);
        $message = "DOM lane alarm cleared on interface $port->ifDescr lane 0. Current alarm(s): output signal high";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomLaneAlarmCleared');
    }
}
