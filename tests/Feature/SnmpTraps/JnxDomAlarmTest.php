<?php
/**
 * JnxDomAlarmTest.php
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

namespace LibreNMS\Tests;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxDomAlarmTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testJnxDomAlarmSetTrap()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomAlarmSet
IF-MIB::ifDescr.663 et-5/2/0
JUNIPER-DOM-MIB::jnxDomLastAlarms.663 \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarms.663 \"80 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarmDate.663 2019-4-17,0:4:51.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "DOM alarm set for interface et-5/2/0. Current alarm(s): input loss of signal";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomAlarmSet');
    }

    public function testJnxDomAlarmClearTrap()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 JUNIPER-DOM-MIB::jnxDomAlarmCleared
IF-MIB::ifDescr.222 xe-0/0/2
JUNIPER-DOM-MIB::jnxDomLastAlarms.222 \"00 00 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarms.222 \"E8 01 00 \"
JUNIPER-DOM-MIB::jnxDomCurrentAlarmDate.222 2019-4-17,0:4:51.0,-5:0
SNMPv2-MIB::snmpTrapEnterprise.0 JUNIPER-CHASSIS-DEFINES-MIB::jnxProductNameMX480";

        $trap = new Trap($trapText);
        $message = "DOM alarm cleared for interface xe-0/0/2. Cleared alarm(s): input loss of signal, input loss of lock, input rx path not ready, input laser power low, module not ready";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle JnxDomAlarmCleared');
    }
}
