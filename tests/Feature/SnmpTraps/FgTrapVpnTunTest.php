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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\BgpPeer;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\LaravelTestCase;

class FgTrapVpnTunTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testVpnTunDown()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapVpnTunDown
FORTINET-CORE-MIB::fnSysSerial.0 FakeSN983832982378
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgVpnTrapLocalGateway.0 10.10.10.10
FORTINET-FORTIGATE-MIB::fgVpnTrapRemoteGateway.0 192.168.20.20
FORTINET-FORTIGATE-MIB::fgVpnTrapPhase1Name.0 test_tunnel_down";

        $message = "VPN tunnel test_tunnel_down to 192.168.20.20 is down";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapVpnTunDown');
    }

    public function testVpnTunUp()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapVpnTunUp
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgVpnTrapLocalGateway.0 10.10.10.10
FORTINET-FORTIGATE-MIB::fgVpnTrapRemoteGateway.0 172.16.29.29
FORTINET-FORTIGATE-MIB::fgVpnTrapPhase1Name.0 test_tunnel_up";

        $message = "VPN tunnel test_tunnel_up to 172.16.29.29 is up";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapVpnTunUp');
    }
}
