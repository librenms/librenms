<?php
/**
 * UpsTrapsOnBatteryTest.php
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
 * @author     TheGreatDoc
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Sensor;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class UpsTrapsOnBatteryTest extends SnmpTrapTestCase
{
    public function testOnBattery()
    {
        $device = Device::factory()->create();
        $state = Sensor::factory()->make(['sensor_class' => 'state', 'sensor_type' => 'upsOutputSourceState', 'sensor_current' => '2']);
        $time = Sensor::factory()->make(['sensor_class' => 'runtime', 'sensor_index' => '100', 'sensor_type' => 'rfc1628', 'sensor_current' => '0']);
        $remaining = Sensor::factory()->make(['sensor_class' => 'runtime', 'sensor_index' => '200', 'sensor_type' => 'rfc1628', 'sensor_current' => '371']);
        $device->sensors()->save($state);
        $device->sensors()->save($time);
        $device->sensors()->save($remaining);

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 9:22:15:00.01
SNMPv2-MIB::snmpTrapOID.0 UPS-MIB::upsTraps.0.1
UPS-MIB::upsEstimatedMinutesRemaining.0 100 minutes
UPS-MIB::upsSecondsOnBattery.0 120 seconds
UPS-MIB::upsConfigLowBattTime.0 1 minutes";

        \Log::shouldReceive('warning')->never()->with("Snmptrap UpsTraps: Could not find matching sensor \'Estimated battery time remaining\' for device: " . $device->hostname);
        \Log::shouldReceive('warning')->never()->with("Snmptrap UpsTraps: Could not find matching sensor \'Time on battery\' for device: " . $device->hostname);
        \Log::shouldReceive('warning')->never()->with("Snmptrap UpsTraps: Could not find matching sensor \'upsOutputSourceState\' for device: " . $device->hostname);

        $message = 'UPS running on battery for 120 seconds. Estimated 100 minutes remaining';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle UPS-MIB::upsTraps.0.1 trap');

        $state = $state->fresh();
        $time = $time->fresh();
        $remaining = $remaining->fresh();
        $this->assertEquals($state->sensor_current, '5');
        $this->assertEquals($time->sensor_current, '120');
        $this->assertEquals($remaining->sensor_current, '100');
    }
}
