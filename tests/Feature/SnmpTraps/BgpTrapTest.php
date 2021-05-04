<?php
/**
 * BgpTrapTest.php
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

use App\Models\BgpPeer;
use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class BgpTrapTest extends SnmpTrapTestCase
{
    public function testBgpUp()
    {
        // Cache it to avoid DNS Lookup
        Config::set('astext.1', 'PHPUnit ASTEXT');
        $device = Device::factory()->create();
        $bgppeer = BgpPeer::factory()->make(['bgpPeerState' => 'idle', 'bgpPeerRemoteAs' => 1]);
        $device->bgppeers()->save($bgppeer);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 BGP4-MIB::bgpEstablished
BGP4-MIB::bgpPeerLastError.$bgppeer->bgpPeerIdentifier \"04 00 \"
BGP4-MIB::bgpPeerState.$bgppeer->bgpPeerIdentifier established\n";

        $message = "SNMP Trap: BGP Up $bgppeer->bgpPeerIdentifier " . get_astext($bgppeer->bgpPeerRemoteAs) . ' is now established';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'bgpPeer', 1, $bgppeer->bgpPeerIdentifier);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle bgpEstablished');

        $bgppeer = $bgppeer->fresh(); // refresh from database
        $this->assertEquals($bgppeer->bgpPeerState, 'established');
    }

    public function testBgpDown()
    {
        // Cache it to avoid DNS Lookup
        Config::set('astext.1', 'PHPUnit ASTEXT');
        $device = Device::factory()->create();
        $bgppeer = BgpPeer::factory()->make(['bgpPeerState' => 'established', 'bgpPeerRemoteAs' => 1]);
        $device->bgppeers()->save($bgppeer);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[185.29.68.52]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:55:33.47
SNMPv2-MIB::snmpTrapOID.0 BGP4-MIB::bgpBackwardTransition
BGP4-MIB::bgpPeerLastError.$bgppeer->bgpPeerIdentifier \"04 00 \"
BGP4-MIB::bgpPeerState.$bgppeer->bgpPeerIdentifier idle\n";

        $message = "SNMP Trap: BGP Down $bgppeer->bgpPeerIdentifier " . get_astext($bgppeer->bgpPeerRemoteAs) . ' is now idle';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'bgpPeer', 5, $bgppeer->bgpPeerIdentifier);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle bgpBackwardTransition');

        $bgppeer = $bgppeer->fresh(); // refresh from database
        $this->assertEquals($bgppeer->bgpPeerState, 'idle');
    }
}
