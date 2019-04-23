<?php
/**
 * JnxBgpM2Test.php
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
 * Tests Junipers BGPv4 traps, specificaly jnxBgpM2Established and
 * jnxBgpM2BackwardTransition
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxBgpM2Test extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testBgpBackwardTrasition()
    {

        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 BGP4-V2-MIB-JUNIPER::jnxBgpM2BackwardTransition
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddrType.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 ipv6
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddr.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"20 01 0D 88 00 01 00 00 00 00 00 00 00 00 00 01 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddrType.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 ipv6
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddr.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"20 01 0D 88 00 01 00 00 00 00 00 00 00 00 00 02 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceived.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"00 00 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceivedText.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerState.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 idle
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240";

        $trap = new Trap($trapText);
        $message = "BGP Peer 2001:0D88:0001:0000:0000:0000:0000:0002 is now in the idle state";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 3);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxBgpM2BackwardsTransition trap');
    }

    public function testBgpEstablished()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 BGP4-V2-MIB-JUNIPER::jnxBgpM2Established
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddrType.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 ipv6
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddr.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"20 01 0D 88 00 01 00 00 00 00 00 00 00 00 00 01 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddrType.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 ipv6
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddr.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"20 01 0D 88 00 01 00 00 00 00 00 00 00 00 00 02 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceived.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 \"00 00 \"
BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerState.0.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.1.2.32.1.13.136.0.1.0.0.0.0.0.0.0.0.0.2 established
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240";

        $trap = new Trap($trapText);
        $message = "BGP Peer 2001:0D88:0001:0000:0000:0000:0000:0002 is now in the established state";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxBgpM2Established trap');
    }
}
