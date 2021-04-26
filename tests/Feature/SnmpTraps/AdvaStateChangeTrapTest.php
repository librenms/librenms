<?php
/**
 * AdvaStateChangeTrapTest.php
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
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class AdvaStateChangeTrapTest extends SnmpTrapTestCase
{
    public function testAccessPortChg()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmEthernetAccPortAdminState.1.1.1.3 maintenance
CM-FACILITY-MIB::cmEthernetAccPortOperationalState.1.1.1.3 normal
CM-FACILITY-MIB::cmEthernetAccPortSecondaryState.1.1.1.3 \"42 00 00 \"
IF-MIB::ifName.3 ACCESS PORT-1-1-1-3
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 14 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.48 48
ADVA-MIB::neEventLogTimeStamp.48 2018-12-10,11:20:40.7,-6:0";

        $trap = new Trap($trapText);

        $message = 'Port state change: ACCESS PORT-1-1-1-3 Admin State: maintenance Operational State: normal';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmStateChangeTrap access port amdmin state maintenance and op state normal');
    }

    public function testNetworkPortChg()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmEthernetNetPortAdminState.1.1.1.2 maintenance
CM-FACILITY-MIB::cmEthernetNetPortOperationalState.1.1.1.2 outage
CM-FACILITY-MIB::cmEthernetNetPortSecondaryState.1.1.1.2 \"52 00 00 \"
IF-MIB::ifName.2 NETWORK PORT-1-1-1-2
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 11 07 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.19 19
ADVA-MIB::neEventLogTimeStamp.19 2018-12-10,11:17:7.9,-6:0";

        $trap = new Trap($trapText);

        $message = 'Port state change: NETWORK PORT-1-1-1-2 Admin State: maintenance Operational State: outage';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmStateChangeTrap access port amdmin state maintenance and op state normal');
    }

    public function testFlowStateChg()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmFlowAdminState.1.1.1.3.1 management
CM-FACILITY-MIB::cmFlowOperationalState.1.1.1.3.1 normal
CM-FACILITY-MIB::cmFlowSecondaryState.1.1.1.3.1 \"40 00 00 \"
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 14 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.50 50
ADVA-MIB::neEventLogTimeStamp.50 2018-12-10,11:20:40.8,-6:0";

        $trap = new Trap($trapText);

        $message = 'Flow state change: 1-1-1-3-1 Admin State: management Operational State: normal';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmStateChangeTrap access port amdmin state maintenance and op state normal');
    }
}
