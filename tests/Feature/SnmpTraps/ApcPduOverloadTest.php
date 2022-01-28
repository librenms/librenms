<?php
/**
 * ApcPduOverloadTest.php
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
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class ApcPduOverloadTest extends SnmpTrapTestCase
{
    public function testNearOverload()
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance:318:0:09:38.28
PowerNet-MIB::rPDUIdentSerialNumber.0:\"5A1036E02224\"
PowerNet-MIB::rPDUIdentName.0:\"Grand POP PDU R15 A1\"
PowerNet-MIB::rPDULoadStatusPhaseNumber.0:1
PowerNet-MIB::mtrapargsString.0:\"Metered Rack PDU: Near overload.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0:$device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0:\"Kr5nMp69\"
SNMPv2-MIB::snmpTrapEnterprise.0:PowerNet-MIB::apc";

        $message = 'Grand POP PDU R15 A1 phase 1 Metered Rack PDU: Near overload.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle rPDUNearOverload trap');
    }

    public function testNearOverloadClear()
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance:318:0:09:38.28
PowerNet-MIB::rPDUIdentSerialNumber.0:\"5A1036E02224\"
PowerNet-MIB::rPDUIdentName.0:\"Grand POP PDU R15 A1\"
PowerNet-MIB::rPDULoadStatusPhaseNumber.0:1
PowerNet-MIB::mtrapargsString.0:\"Metered Rack PDU: Near overload cleared.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0:$device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0:\"Kr5nMp69\"
SNMPv2-MIB::snmpTrapEnterprise.0:PowerNet-MIB::apc";

        $message = 'Grand POP PDU R15 A1 phase 1 Metered Rack PDU: Near overload cleared.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle rPDUNearOverloadClear trap');
    }

    public function testOverload()
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance:318:0:09:38.28
PowerNet-MIB::rPDUIdentSerialNumber.0:\"5A1036E02224\"
PowerNet-MIB::rPDUIdentName.0:\"Grand POP PDU R15 A1\"
PowerNet-MIB::rPDULoadStatusPhaseNumber.0:1
PowerNet-MIB::mtrapargsString.0:\"APC Rack PDU: Overload condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0:$device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0:\"Kr5nMp69\"
SNMPv2-MIB::snmpTrapEnterprise.0:PowerNet-MIB::apc";

        $message = 'Grand POP PDU R15 A1 phase 1 APC Rack PDU: Overload condition.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle rPDUOverload trap');
    }

    public function testOverloadClear()
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance:318:0:09:38.28
PowerNet-MIB::rPDUIdentSerialNumber.0:\"5A1036E02224\"
PowerNet-MIB::rPDUIdentName.0:\"Grand POP PDU R15 A1\"
PowerNet-MIB::rPDULoadStatusPhaseNumber.0:1
PowerNet-MIB::mtrapargsString.0:\"APC Rack PDU: Overload condition has cleared.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0:$device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0:\"Kr5nMp69\"
SNMPv2-MIB::snmpTrapEnterprise.0:PowerNet-MIB::apc";

        $message = 'Grand POP PDU R15 A1 phase 1 APC Rack PDU: Overload condition has cleared.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle rPDUOverloadClear trap');
    }
}
