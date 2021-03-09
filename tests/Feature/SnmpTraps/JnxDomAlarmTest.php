<?php
/**
 * JnxDomAlarmTest.php
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

class JnxDomAlarmTest extends SnmpTrapTestCase
{
    public function testJnxDomAlarmSetTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomAlarmSet
IF-MIB::ifDescr.$port->ifIndex $port->ifDescr
JUNIPER-DOM-MIB::jnxDomLastAlarms.$port->ifIndex \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarms.$port->ifIndex \"80 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarmDate.$port->ifIndex 2019-4-17,0:4:51.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "DOM alarm set for interface $port->ifDescr. Current alarm(s): input loss of signal";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomAlarmSet');
    }

    public function testJnxDomAlarmClearTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomAlarmCleared
IF-MIB::ifDescr.$port->ifIndex $port->ifDescr
JUNIPER-DOM-MIB::jnxDomLastAlarms.$port->ifIndex \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarms.$port->ifIndex \"E8 01 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarmDate.$port->ifIndex 2019-4-17,0:4:51.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "DOM alarm cleared for interface $port->ifDescr. Cleared alarm(s): input loss of signal, input loss of lock, input rx path not ready, input laser power low, module not ready";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomAlarmCleared');
    }
}
