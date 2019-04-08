<?php
/**
 * AdvaTeholdCrossingAlertTest.php
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
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\LaravelTestCase;

class AdvaNetThresholdCrossingAlertTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testNetThresholdTrap()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdIndex.1.1.1.2.1.37 37
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdInterval.1.1.1.2.1.37 interval-15min
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdVariable.1.1.1.2.1.37 CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS.1.1.1.2.1
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdValueLo.1.1.1.2.1.37 10
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdValueHi.1.1.1.2.1.37 0
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdMonValue.1.1.1.2.1.37 10
IF-MIB::ifName.2 NETWORK PORT-1-1-1-2
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 0B 2D 0A 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.79 79
ADVA-MIB::neEventLogTimeStamp.79 2018-12-10,11:45:10.8,-6:0";

        $trap = new Trap($trapText);

        $message = "NETWORK PORT-1-1-1-2 threshold exceeded for interval-15min. Threshold OID is CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS.1.1.1.2.1.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmEthernetNetPortThresholdCrossingAlert');
    }
}
