<?php
/*
 * FmTrapLogRateThresholdTest.php
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
 * Unit tests for Fortigate FmTrapLogRateThreshold.php
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class FmTrapLogRateThresholdTest extends SnmpTrapTestCase
{
    public function testAvOversize()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIMANAGER-FORTIANALYZER-MIB::fmTrapLogRateThreshold
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIMANAGER-FORTIANALYZER-MIB::fmLogRate.0 315
FORTINET-FORTIMANAGER-FORTIANALYZER-MIB::fmLogRateThreshold.0 260";

        $message = 'Recommended log rate exceeded. Current Rate: 315 Recommended Rate: 260';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fmTrapLogRateThreshold trap');
    }
}
