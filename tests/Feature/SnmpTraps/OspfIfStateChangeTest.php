<?php
/**
 * OspfIfStateChangeTest.php
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

use App\Models\Device;
use App\Models\Port;
use App\Models\OspfPort;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class OspfIfStateChangeTest extends SnmpTrapTestCase
{
    public function testOspfIfDown()
    {
        // make a device and associate a port with it
        $device = factory(Device::class)->create();
        $port = factory(Port::class)->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up', 'ifName' => 'int1.0']);
        
        $device->ports()->save($port);

        $ospfIf = factory(OspfPort::class)->make(['port_id' => $port->port_id, 'ospfIfState' => 'designatedRouter']);
        $device->ospfPorts()->save($ospfIf);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:6:11:31.55
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfIfStateChange
OSPF-MIB::ospfRouterId.0 $device->ip
OSPF-MIB::ospfIfIpAddress.$ospfIf->ospfIfIpAddress.0 $ospfIf->ospfIfIpAddress
OSPF-MIB::ospfAddressLessIf.$ospfIf->ospfIfIpAddress.0 $ospfIf->ospfAddressLessIf
OSPF-MIB::ospfIfState.$ospfIf->ospfIfIpAddress.0 down
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240";

        $trap = new Trap($trapText);

        $message = "OSPF interface $port->ifName is down";
        
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ospfIfStateChange down');

        $ospfIf = $ospfIf->fresh();
        $this->assertEquals($ospfIf->ospfIfState, 'down');
    }
}
