<?php
/**
 * JnxLdpLspTest.php
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
 * Tests JnxLdpLspDown and JnxLdpLspUp traps from Juniper devices.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class JnxLdpLspTest extends SnmpTrapTestCase
{
    public function testLdpLspDownTrap()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-LDP-MIB::jnxLdpLspDown
JUNIPER-LDP-MIB::jnxLdpLspFec.0 $ipv4->ipv4_address
JUNIPER-LDP-MIB::jnxLdpRtrid.0 $device->ip
JUNIPER-LDP-MIB::jnxLdpLspDownReason.0 topologyChanged
JUNIPER-LDP-MIB::jnxLdpLspFecLen.0 32
JUNIPER-LDP-MIB::jnxLdpInstanceName.0 \"test instance down\"
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "LDP session test instance down from $device->ip to $ipv4->ipv4_address has gone down due to topologyChanged";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxLdpLspDown trap');
    }

    public function testLdpLspUpTrap()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-LDP-MIB::jnxLdpLspUp
JUNIPER-LDP-MIB::jnxLdpLspFec.0 $ipv4->ipv4_address
JUNIPER-LDP-MIB::jnxLdpRtrid.0 $device->ip
JUNIPER-LDP-MIB::jnxLdpLspFecLen.0 32
JUNIPER-LDP-MIB::jnxLdpInstanceName.0 \"test instance up\"
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "LDP session test instance up from $device->ip to $ipv4->ipv4_address is now up.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxLdpLspUp trap');
    }
}
