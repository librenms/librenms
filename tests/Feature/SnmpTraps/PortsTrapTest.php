<?php
/**
 * PortsTrapTest.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class PortsTrapTest extends SnmpTrapTestCase
{
    public function testLinkDown()
    {
        // make a device and associate a port with it
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "<UNKNOWN>
UDP: [$device->ip]:57123->[192.168.4.4]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:12.87
SNMPv2-MIB::snmpTrapOID.0 IF-MIB::linkDown
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
IF-MIB::ifAdminStatus.$port->ifIndex down
IF-MIB::ifOperStatus.$port->ifIndex down
IF-MIB::ifDescr.$port->ifIndex GigabitEthernet0/5
IF-MIB::ifType.$port->ifIndex ethernetCsmacd
OLD-CISCO-INTERFACES-MIB::locIfReason.$port->ifIndex \"down\"\n";

        Log::shouldReceive('event')->once()->with('SNMP Trap: linkDown down/down ' . $port->ifDescr, $device->device_id, 'interface', 5, $port->port_id);
        Log::shouldReceive('event')->once()->with("Interface Disabled : $port->ifDescr (TRAP)", $device->device_id, 'interface', 3, $port->port_id);
        Log::shouldReceive('event')->once()->with("Interface went Down : $port->ifDescr (TRAP)", $device->device_id, 'interface', 5, $port->port_id);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle linkDown');

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'down');
        $this->assertEquals($port->ifOperStatus, 'down');
    }

    public function testLinkUp()
    {
        // make a device and associate a port with it
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'down', 'ifOperStatus' => 'down']);
        $device->ports()->save($port);

        $trapText = "<UNKNOWN>
UDP: [$device->ip]:57123->[185.29.68.52]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:18.21
SNMPv2-MIB::snmpTrapOID.0 IF-MIB::linkUp
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
IF-MIB::ifAdminStatus.$port->ifIndex up
IF-MIB::ifOperStatus.$port->ifIndex up
IF-MIB::ifDescr.$port->ifIndex GigabitEthernet0/5
IF-MIB::ifType.$port->ifIndex ethernetCsmacd
OLD-CISCO-INTERFACES-MIB::locIfReason.$port->ifIndex \"up\"\n";

        Log::shouldReceive('event')->once()->with('SNMP Trap: linkUp up/up ' . $port->ifDescr, $device->device_id, 'interface', 1, $port->port_id);
        Log::shouldReceive('event')->once()->with("Interface Enabled : $port->ifDescr (TRAP)", $device->device_id, 'interface', 3, $port->port_id);
        Log::shouldReceive('event')->once()->with("Interface went Up : $port->ifDescr (TRAP)", $device->device_id, 'interface', 1, $port->port_id);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle linkUp');

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'up');
        $this->assertEquals($port->ifOperStatus, 'up');
    }
}
