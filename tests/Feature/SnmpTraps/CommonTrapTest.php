<?php
/**
 * CommonTrapTest.php
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
use App\Models\Port;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class CommonTrapTest extends SnmpTrapTestCase
{
    public function testGarbage()
    {
        $trapText = "Garbage\n";

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');
    }

    public function testFindByIp()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make();
        $device->ports()->save($port);
        $ipv4 = Ipv4Address::factory()->make(); // test ipv4 lookup of device
        $port->ipv4()->save($ipv4);

        $trapText = "something
UDP: [$ipv4->ipv4_address]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91\n";

        Log::shouldReceive('info')->once()->with('Unhandled trap snmptrap', ['device' => $device->hostname, 'oid' => null]);
        Log::shouldReceive('event')->once()->withArgs(function ($e_message, $e_device, $e_type) use ($device) {
            return $e_message == '' &&
                $device->is($e_device) &&
                $e_type == 'trap';
        });

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testGenericTrap()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::someOid\n";

        Log::shouldReceive('info')->once()->with('Unhandled trap snmptrap', ['device' => $device->hostname, 'oid' => 'SNMPv2-MIB::someOid']);
        Log::shouldReceive('event')->once()->withArgs(function ($e_message, $e_device, $e_type) use ($device) {
            return $e_message == 'SNMPv2-MIB::someOid' &&
                $device->is($e_device) &&
                $e_type == 'trap';
        });

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap));
    }

    public function testAuthorization()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::authenticationFailure\n";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Authentication Failure: ' . $device->displayName(), $device->device_id, 'auth', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testBridgeNewRoot()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 BRIDGE-MIB::newRoot";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Device ' . $device->displayName() . ' was elected as new root on one of its Spanning Tree Instances', $device->device_id, 'stp', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testBridgeTopologyChanged()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 BRIDGE-MIB::topologyChange";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Topology of Spanning Tree Instance on device ' . $device->displayName() . ' was changed', $device->device_id, 'stp', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testColdStart()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:1:12.7
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::coldStart";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Device ' . $device->displayName() . ' cold booted', $device->device_id, 'reboot', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testWarmStart()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:2:12.7
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::warmStart";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Device ' . $device->displayName() . ' warm booted', $device->device_id, 'reboot', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }

    public function testEntityDatabaseChanged()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 ENTITY-MIB::entConfigChange";

        Log::shouldReceive('event')->once()->with('SNMP Trap: Configuration of Entity Database on device ' . $device->displayName() . ' was changed', $device->device_id, 'system', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);
    }
}
