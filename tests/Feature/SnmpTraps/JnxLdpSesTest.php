<?php
/**
 * JnxLdpSesTest.php
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
 * Tests JnxLdpSesDown and JnxLdpSesUp traps from Juniper devices.
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

class JnxLdpSesTest extends SnmpTrapTestCase
{
    public function testJnxLdpSesDownTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-LDP-MIB::jnxLdpSesDown
JUNIPER-MPLS-LDP-MIB::jnxMplsLdpSesState.'.q.j..'.1.'.q.p..' nonexistent
JUNIPER-LDP-MIB::jnxLdpSesDownReason.0 allAdjacenciesDown
JUNIPER-LDP-MIB::jnxLdpSesDownIf.0 $port->ifIndex
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        \Log::shouldReceive('warning')->never()->with("Snmptrap LdpSesDown: Could not find port at ifIndex $port->ifIndex for device: $device->hostname");

        $trap = new Trap($trapText);
        $message = "LDP session on interface $port->ifDescr is nonexistent due to allAdjacenciesDown";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxLdpSesDown trap');
    }

    public function testJnxLdpSesUpTrap()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-LDP-MIB::jnxLdpSesUp
JUNIPER-MPLS-LDP-MIB::jnxMplsLdpSesState.'.q.d..'.1.'.q.p..' operational
JUNIPER-LDP-MIB::jnxLdpSesUpIf.0 $port->ifIndex
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX960";

        \Log::shouldReceive('warning')->never()->with("Snmptrap LdpSesUp: Could not find port at ifIndex $port->ifIndex for device: $device->hostname");

        $trap = new Trap($trapText);
        $message = "LDP session on interface $port->ifDescr is operational";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxLdpSesUp trap');
    }
}
