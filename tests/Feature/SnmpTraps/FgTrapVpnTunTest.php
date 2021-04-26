<?php
/**
 * FgTrapVpnTunTest.php
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
use App\Models\Ipv4Address;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class FgTrapVpnTunTest extends SnmpTrapTestCase
{
    public function testVpnTunDown()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapVpnTunDown
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgVpnTrapLocalGateway.0 $device->ip
FORTINET-FORTIGATE-MIB::fgVpnTrapRemoteGateway.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgVpnTrapPhase1Name.0 test_tunnel_down";

        $message = "VPN tunnel test_tunnel_down to $ipv4->ipv4_address is down";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapVpnTunDown');
    }

    public function testVpnTunUp()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapVpnTunUp
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgVpnTrapLocalGateway.0 $device->ip
FORTINET-FORTIGATE-MIB::fgVpnTrapRemoteGateway.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgVpnTrapPhase1Name.0 test_tunnel_up";

        $message = "VPN tunnel test_tunnel_up to $ipv4->ipv4_address is up";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapVpnTunUp');
    }
}
