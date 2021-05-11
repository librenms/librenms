<?php
/*
 * TrippliteTrapTest.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class TrippliteTrapTest extends SnmpTrapTestCase
{
    public function testTlpNotificationsAlarmEntryAdded()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:1:55:34.92
SNMPv2-MIB::snmpTrapOID.0 TRIPPLITE-PRODUCTS::tlpNotificationsAlarmEntryAdded
TRIPPLITE-PRODUCTS::tlpAlarmId 6
TRIPPLITE-PRODUCTS::tlpAlarmDescr TRIPPLITE-PRODUCTS::tlpUpsAlarmOnBattery
TRIPPLITE-PRODUCTS::tlpAlarmTime 0:1:56:20.44
TRIPPLITE-PRODUCTS::tlpAlarmTableRef TRIPPLITE-PRODUCTS::tlpDeviceTable
TRIPPLITE-PRODUCTS::tlpAlarmTableRowRef TRIPPLITE-PRODUCTS::tlpDeviceIndex.1
TRIPPLITE-PRODUCTS::tlpAlarmDetail On Battery
TRIPPLITE-PRODUCTS::tlpAlarmType warning
TRIPPLITE-PRODUCTS::tlpAlarmState active
TRIPPLITE-PRODUCTS::tlpDeviceName.1 $device->sysDescr
TRIPPLITE-PRODUCTS::tlpDeviceLocation.1 $device->location
TRIPPLITE-PRODUCTS::tlpAgentMAC.0 00:06:67:AE:BE:13
TRIPPLITE-PRODUCTS::tlpAgentUuid.0 c94e376a-8080-44fb-96ad-0fe6583d1c4a";

        $trap = new Trap($trapText);

        $message = 'Trap Alarm active: On Battery';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle tlpNotificationsAlarmEntryAdded');
    }

    public function testTlpNotificationsAlarmEntryRemoved()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:1:56:40.26
SNMPv2-MIB::snmpTrapOID.0 TRIPPLITE-PRODUCTS::tlpNotificationsAlarmEntryRemoved
TRIPPLITE-PRODUCTS::tlpAlarmId 6
TRIPPLITE-PRODUCTS::tlpAlarmDescr TRIPPLITE-PRODUCTS::tlpUpsAlarmOnBattery
TRIPPLITE-PRODUCTS::tlpAlarmTime 0:1:56:20.44
TRIPPLITE-PRODUCTS::tlpAlarmTableRef TRIPPLITE-PRODUCTS::tlpDeviceTable
TRIPPLITE-PRODUCTS::tlpAlarmTableRowRef TRIPPLITE-PRODUCTS::tlpDeviceIndex.1
TRIPPLITE-PRODUCTS::tlpAlarmDetail On Utility Power
TRIPPLITE-PRODUCTS::tlpAlarmType info
TRIPPLITE-PRODUCTS::tlpAlarmState inactive
TRIPPLITE-PRODUCTS::tlpDeviceName.1 $device->sysDescr
TRIPPLITE-PRODUCTS::tlpDeviceLocation.1 $device->location
TRIPPLITE-PRODUCTS::tlpAgentMAC.0 00:06:67:AE:BE:13
TRIPPLITE-PRODUCTS::tlpAgentUuid.0 c94e376a-8080-44fb-96ad-0fe6583d1c4a";

        $trap = new Trap($trapText);

        $message = 'Trap Alarm inactive: On Utility Power';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle tlpNotificationsAlarmEntryRemoved');
    }
}
