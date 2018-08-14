<?php
/**
 * SnmpTrapTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class SnmpTrapTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testGarbage()
    {
        $trapText = "Garbage\n";

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');
    }

    public function testFindByIp()
    {
        $device = factory(Device::class)->create();
        $port = factory(Port::class)->make();
        $device->ports()->save($port);
        $ipv4 = factory(Ipv4Address::class)->make(); // test ipv4 lookup of device
        $port->ipv4()->save($ipv4);

        $trapText = "something
UDP: [$ipv4->ipv4_address]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91\n";

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testAuthorization()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::authenticationFailure\n";

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);

//        $event = \App\Models\LogEvent::orderBy('datetime', 'desc')->first();

//        dd($event);
    }

    public function testBgpUp()
    {
        $device = factory(Device::class)->create();
        $bgppeer = factory(BgpPeer::class)->make(['bgpPeerState' => 'idle']);
        $device->bgppeers()->save($bgppeer);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 BGP4-MIB::bgpEstablished
BGP4-MIB::bgpPeerLastError.$bgppeer->bgpPeerIdentifier \"04 00 \"
BGP4-MIB::bgpPeerState.$bgppeer->bgpPeerIdentifier established\n";

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle bgpEstablished');

        $bgppeer = $bgppeer->fresh(); // refresh from database
        $this->assertEquals($bgppeer->bgpPeerState, 'established');
    }

    public function testBgpDown()
    {
        $device = factory(Device::class)->create();
        $bgppeer = factory(BgpPeer::class)->make(['bgpPeerState' => 'established']);
        $device->bgppeers()->save($bgppeer);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[185.29.68.52]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:55:33.47
SNMPv2-MIB::snmpTrapOID.0 BGP4-MIB::bgpBackwardTransition
BGP4-MIB::bgpPeerLastError.$bgppeer->bgpPeerIdentifier \"04 00 \"
BGP4-MIB::bgpPeerState.$bgppeer->bgpPeerIdentifier idle\n";

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle bgpBackwardTransition');

        $bgppeer = $bgppeer->fresh(); // refresh from database
        $this->assertEquals($bgppeer->bgpPeerState, 'idle');
    }

    public function testLinkDown()
    {
        // make a device and associate a port with it
        $device = factory(Device::class)->create();
        $port = factory(Port::class)->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
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

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle linkDown');


        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'down');
        $this->assertEquals($port->ifOperStatus, 'down');
    }

    public function testLinkUp()
    {
        // make a device and associate a port with it
        $device = factory(Device::class)->create();
        $port = factory(Port::class)->make(['ifAdminStatus' => 'down', 'ifOperStatus' => 'down']);
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

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle linkUp');

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'up');
        $this->assertEquals($port->ifOperStatus, 'up');
    }
}
