<?php
/**
 * AdvaAttributeChangeTest.php
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

class AdvaAttributeChangeTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testSyslogIPVersion()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpVersion.1 ipv6
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 0B 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0";

        $trap = new Trap($trapText);

        $message = "Syslog server 1 IP version set to ipv6.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmAttributeValueChangeTrap IP version change');
    }

    public function testSyslogIP6Addr()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpv6Addr.1 2001:49d0:3c0c:0:0:0:0:1
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 0B 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0";

        $trap = new Trap($trapText);

        $message = "Syslog server 1 IP address changed to 2001:49d0:3c0c:0:0:0:0:1.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmAttributeValueChangeTrap IP address change');
    }

    public function testSyslogIPAddr()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpAddress.1 192.168.1.1
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 0B 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0";

        $trap = new Trap($trapText);

        $message = "Syslog server 1 IP address changed to 192.168.1.1.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmAttributeValueChangeTrap IPv4 address change');
    }

    public function testSyslogPort()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogPort.1 514
RMON2-MIB::probeDateTime.0 \"07 E2 0C 0A 09 0B 28 00 2D 06 00 \"
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0";

        $trap = new Trap($trapText);

        $message = "Syslog server 1 port changed to 514.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmAttributeValueChangeTrap port change');
    }
}
