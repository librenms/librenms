<?php
/**
 * AdvaNetworkElementAlmTest.php
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

class AdvaNetworkElementAlmTrapTest extends SnmpTrapTestCase
{
    public function testElementAlarmCleared()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmNetworkElementAlmTrap
CM-ALARM-MIB::cmAlmIndex.0 30
CM-ALARM-MIB::cmNetworkElementAlmNotifCode.1.30 cleared
CM-ALARM-MIB::cmNetworkElementAlmType.1.30 lnkdown
CM-ALARM-MIB::cmNetworkElementAlmSrvEff.1.30 serviceAffecting
CM-ALARM-MIB::cmNetworkElementAlmTime.1.30 2018-12-10,11:1:43.0,-6:0
CM-ALARM-MIB::cmNetworkElementAlmLocation.1.30 nearEnd
CM-ALARM-MIB::cmNetworkElementAlmDirection.1.30 receiveDirectionOnly
CM-ALARM-MIB::cmNetworkElementAlmDescr.1.30 \"Test Alarm Cleared\"
CM-ALARM-MIB::cmNetworkElementAlmObject.1.30 CM-FACILITY-MIB::cmEthernetNetPortIndex.1.1.1.2
CM-ALARM-MIB::cmNetworkElementAlmObjectName.1.30 NETWORK PORT-1-1-1-2
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoObject.1.30 SNMPv2-SMI::zeroDotZero
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoName.1.30 
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 01 2B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.231 231
ADVA-MIB::neEventLogTimeStamp.231 2018-12-10,11:1:43.3,-6:0";

        $trap = new Trap($trapText);

        $message = 'Alarming Element: NETWORK PORT-1-1-1-2 Description: Test Alarm Cleared Severity: cleared';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmNetworkElementAlmTrap cleared');
    }

    public function testElementAlarmMinor()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmNetworkElementAlmTrap
CM-ALARM-MIB::cmAlmIndex.0 30
CM-ALARM-MIB::cmNetworkElementAlmNotifCode.1.30 minor
CM-ALARM-MIB::cmNetworkElementAlmType.1.30 lnkdown
CM-ALARM-MIB::cmNetworkElementAlmSrvEff.1.30 serviceAffecting
CM-ALARM-MIB::cmNetworkElementAlmTime.1.30 2018-12-10,11:1:43.0,-6:0
CM-ALARM-MIB::cmNetworkElementAlmLocation.1.30 nearEnd
CM-ALARM-MIB::cmNetworkElementAlmDirection.1.30 receiveDirectionOnly
CM-ALARM-MIB::cmNetworkElementAlmDescr.1.30 \"Test Alarm Minor\"
CM-ALARM-MIB::cmNetworkElementAlmObject.1.30 CM-FACILITY-MIB::cmEthernetNetPortIndex.1.1.1.2
CM-ALARM-MIB::cmNetworkElementAlmObjectName.1.30 NETWORK PORT-1-1-1-2
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoObject.1.30 SNMPv2-SMI::zeroDotZero
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoName.1.30 
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 01 2B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.231 231
ADVA-MIB::neEventLogTimeStamp.231 2018-12-10,11:1:43.3,-6:0";

        $trap = new Trap($trapText);

        $message = 'Alarming Element: NETWORK PORT-1-1-1-2 Description: Test Alarm Minor Severity: minor';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 3);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmNetworkElementAlmTrap minor');
    }

    public function testElementAlarmMajor()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmNetworkElementAlmTrap
CM-ALARM-MIB::cmAlmIndex.0 30
CM-ALARM-MIB::cmNetworkElementAlmNotifCode.1.30 major
CM-ALARM-MIB::cmNetworkElementAlmType.1.30 lnkdown
CM-ALARM-MIB::cmNetworkElementAlmSrvEff.1.30 serviceAffecting
CM-ALARM-MIB::cmNetworkElementAlmTime.1.30 2018-12-10,11:1:43.0,-6:0
CM-ALARM-MIB::cmNetworkElementAlmLocation.1.30 nearEnd
CM-ALARM-MIB::cmNetworkElementAlmDirection.1.30 receiveDirectionOnly
CM-ALARM-MIB::cmNetworkElementAlmDescr.1.30 \"Test Alarm Major\"
CM-ALARM-MIB::cmNetworkElementAlmObject.1.30 CM-FACILITY-MIB::cmEthernetNetPortIndex.1.1.1.2
CM-ALARM-MIB::cmNetworkElementAlmObjectName.1.30 NETWORK PORT-1-1-1-2
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoObject.1.30 SNMPv2-SMI::zeroDotZero
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoName.1.30 
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 01 2B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.231 231
ADVA-MIB::neEventLogTimeStamp.231 2018-12-10,11:1:43.3,-6:0";

        $trap = new Trap($trapText);

        $message = 'Alarming Element: NETWORK PORT-1-1-1-2 Description: Test Alarm Major Severity: major';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmNetworkElementAlmTrap major');
    }

    public function testElementAlarmCritical()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmNetworkElementAlmTrap
CM-ALARM-MIB::cmAlmIndex.0 30
CM-ALARM-MIB::cmNetworkElementAlmNotifCode.1.30 critical
CM-ALARM-MIB::cmNetworkElementAlmType.1.30 lnkdown
CM-ALARM-MIB::cmNetworkElementAlmSrvEff.1.30 serviceAffecting
CM-ALARM-MIB::cmNetworkElementAlmTime.1.30 2018-12-10,11:1:43.0,-6:0
CM-ALARM-MIB::cmNetworkElementAlmLocation.1.30 nearEnd
CM-ALARM-MIB::cmNetworkElementAlmDirection.1.30 receiveDirectionOnly
CM-ALARM-MIB::cmNetworkElementAlmDescr.1.30 \"Test Alarm Critical\"
CM-ALARM-MIB::cmNetworkElementAlmObject.1.30 CM-FACILITY-MIB::cmEthernetNetPortIndex.1.1.1.2
CM-ALARM-MIB::cmNetworkElementAlmObjectName.1.30 NETWORK PORT-1-1-1-2
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoObject.1.30 SNMPv2-SMI::zeroDotZero
CM-ALARM-MIB::cmNetworkElementAlmAdditionalInfoName.1.30 
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 01 2B 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.231 231
ADVA-MIB::neEventLogTimeStamp.231 2018-12-10,11:1:43.3,-6:0";

        $trap = new Trap($trapText);

        $message = 'Alarming Element: NETWORK PORT-1-1-1-2 Description: Test Alarm Critical Severity: critical';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmNetworkElementAlmTrap critical');
    }
}
