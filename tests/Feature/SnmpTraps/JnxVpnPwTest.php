<?php
/**
 * JnxVpnPwTest.php
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
 *
 * Tests JnxVpnPwDown and JnxVpnPwUp traps from Juniper devices.
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

class JnxVpnPwTest extends SnmpTrapTestCase
{
    public function testVpnPwDown()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-VPN-MIB::jnxVpnPwDown
JUNIPER-VPN-MIB::jnxVpnPwVpnType.l2Circuit.\"ge-0/0/2.0\".$port->ifIndex l2Circuit
JUNIPER-VPN-MIB::jnxVpnPwVpnName.l2Circuit.\"ge-0/0/2.0\".$port->ifIndex $port->ifDescr
JUNIPER-VPN-MIB::jnxVpnPwIndex.l2Circuit.\"ge-0/0/2.0\".$port->ifIndex $port->ifIndex
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "l2Circuit on a pseudowire belonging to $port->ifDescr has gone down";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxVpnPwDown trap');
    }

    public function testVpnPwUp()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        $device->ports()->save($port);

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-VPN-MIB::jnxVpnPwUp
JUNIPER-VPN-MIB::jnxVpnPwVpnType.l2Circuit.\"ge-0/0/2.0\".$port->ifIndex l2Circuit
JUNIPER-VPN-MIB::jnxVpnPwVpnName.l2Circuit.\"ge-0/0/2.0\".$port->ifIndex $port->ifDescr
JUNIPER-VPN-MIB::jnxVpnPwIndex.l2Circuit.\"ge-0/02.0\".$port->ifIndex $port->ifIndex
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX960";

        $trap = new Trap($trapText);
        $message = "l2Circuit on a pseudowire belonging to $port->ifDescr is now connected";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxVpnPwUp trap');
    }
}
