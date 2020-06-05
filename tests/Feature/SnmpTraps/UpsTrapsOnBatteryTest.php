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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @author     TheGreatDoc
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\DBTestCase;

class UpsTrapsOnBatteryTest extends SnmpTrapTestCase
{
    public function testOnBattery()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 9:22:15:00.01
SNMPv2-MIB::snmpTrapOID.0 UPS-MIB::upsTraps.0.1
UPS-MIB::upsEstimatedMinutesRemaining.0 100 minutes
UPS-MIB::upsSecondsOnBattery.0 120 seconds
UPS-MIB::upsConfigLowBattTime.0 1 minutes";

        $message = "UPS running on battery for 120 seconds. Estimated 100 minutes remaining";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle UPS-MIB::upsTraps.0.1 trap');
    }
}
