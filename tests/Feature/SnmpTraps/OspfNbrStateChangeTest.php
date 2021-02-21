<?php
/**
 * OspfNbrStateChangeTest.php
 *
 * -Description-
 *
 * Unit test for the OspfNbStateChange SNMP trap handler. Will verify
 * trap is properly logged and ospf_nbrs.ospfNbrState is updated in the
 * database.
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
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\OspfNbr;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class OspfNbrStateChangeTest extends SnmpTrapTestCase
{
    //Test OSPF neighbor state down trap
    public function testOspfNbrDown()
    {
        $device = Device::factory()->create();

        $ospfNbr = OspfNbr::factory()->make(['device_id' => $device->device_id, 'ospfNbrState' => 'full']);
        $ospfNbr->ospf_nbr_id = "$ospfNbr->ospfNbrIpAddr.$ospfNbr->ospfNbrAddressLessIndex";
        $device->ospfNbrs()->save($ospfNbr);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:1:07:16.06
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfNbrStateChange
OSPF-MIB::ospfRouterId.0 $device->ip
OSPF-MIB::ospfNbrIpAddr.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrIpAddr
OSPF-MIB::ospfNbrAddressLessIndex.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrAddressLessIndex
OSPF-MIB::ospfNbrRtrId.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrRtrId
OSPF-MIB::ospfNbrState.$ospfNbr->ospf_nbr_id down
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240 ";

        $trap = new Trap($trapText);

        $message = "OSPF neighbor $ospfNbr->ospfNbrRtrId changed state to down";

        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ospfNbrStateChange down');

        $ospfNbr = $ospfNbr->fresh();
        $this->assertEquals($ospfNbr->ospfNbrState, 'down');
    }

    //Test OSPF neighbor state full trap
    public function testOspfNbrFull()
    {
        $device = Device::factory()->create();

        $ospfNbr = OspfNbr::factory()->make(['device_id' => $device->device_id, 'ospfNbrState' => 'down']);
        $ospfNbr->ospf_nbr_id = "$ospfNbr->ospfNbrIpAddr.$ospfNbr->ospfNbrAddressLessIndex";
        $device->ospfNbrs()->save($ospfNbr);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:1:07:16.06
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfNbrStateChange
OSPF-MIB::ospfRouterId.0 $device->ip
OSPF-MIB::ospfNbrIpAddr.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrIpAddr
OSPF-MIB::ospfNbrAddressLessIndex.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrAddressLessIndex
OSPF-MIB::ospfNbrRtrId.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrRtrId
OSPF-MIB::ospfNbrState.$ospfNbr->ospf_nbr_id full
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240 ";

        $trap = new Trap($trapText);

        $message = "OSPF neighbor $ospfNbr->ospfNbrRtrId changed state to full";

        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ospfNbrStateChange full');

        $ospfNbr = $ospfNbr->fresh();
        $this->assertEquals($ospfNbr->ospfNbrState, 'full');
    }

    //Test OSPF neighbor state trap any other state
    public function testOspfNbrOther()
    {
        $device = Device::factory()->create();

        $ospfNbr = OspfNbr::factory()->make(['device_id' => $device->device_id, 'ospfNbrState' => 'full']);
        $ospfNbr->ospf_nbr_id = "$ospfNbr->ospfNbrIpAddr.$ospfNbr->ospfNbrAddressLessIndex";
        $device->ospfNbrs()->save($ospfNbr);

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:1:07:16.06
SNMPv2-MIB::snmpTrapOID.0 OSPF-TRAP-MIB::ospfNbrStateChange
OSPF-MIB::ospfRouterId.0 $device->ip
OSPF-MIB::ospfNbrIpAddr.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrIpAddr
OSPF-MIB::ospfNbrAddressLessIndex.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrAddressLessIndex
OSPF-MIB::ospfNbrRtrId.$ospfNbr->ospf_nbr_id $ospfNbr->ospfNbrRtrId
OSPF-MIB::ospfNbrState.$ospfNbr->ospf_nbr_id exstart
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameSRX240 ";

        $trap = new Trap($trapText);

        $message = "OSPF neighbor $ospfNbr->ospfNbrRtrId changed state to exstart";

        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ospfNbrStateChange exstart');

        $ospfNbr = $ospfNbr->fresh();
        $this->assertEquals($ospfNbr->ospfNbrState, 'exstart');
    }
}
