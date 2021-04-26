<?php
/**
 * RuckusSzEventTest.php
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
 * Tests Ruckus Wireless SmartZone Event trap handlers.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class RuckusSzEventTest extends SnmpTrapTestCase
{
    public function testSzApConf()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPConfUpdatedTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 \"Informational\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 \"110\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 \"apConfUpdated\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 \"$device->hostname\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 \"de:ad:be:ef:33:40\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 \"$device->ip\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 \"$device->location\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 \"$device->sysDescr\"
RUCKUS-SZ-EVENT-MIB::ruckusSZAPConfigID.0 \"2f860f70-6b88-11e9-a3c5-000000937916\"";

        $trap = new Trap($trapText);

        $message = "AP at location $device->location configuration updated with config-id 2f860f70-6b88-11e9-a3c5-000000937916";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ruckusSZAPConfUpdatedTrap');
    }

    public function testSzApConnect()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPConnectedTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 \"Informational\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 \"312\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 \"apConnected\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 \"$device->hostname\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 \"de:ad:be:ef:33:40\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 \"$device->ip\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 \"$device->location\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 \"$device->sysDescr\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventReason.0 \"AP connected after rebooting\"";

        $trap = new Trap($trapText);

        $message = "AP at location $device->location has connected to the SmartZone with reason AP connected after rebooting";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ruckusSZAPConnectedTrap');
    }

    public function testSzApMiscEvent()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPMiscEventTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 \"Minor\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 \"322\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 \"apWLANStateChanged\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 \"$device->hostname\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 \"de:ad:be:ef:33:40\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 \"$device->ip\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 \"$device->location\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 \"$device->sysDescr\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventDescription.0 \"Test AP event has occured\"";

        $trap = new Trap($trapText);

        $message = 'AP event: Test AP event has occured';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ruckusSZAPMiscEventTrap');
    }

    public function testSzApRebooted()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 RUCKUS-SZ-EVENT-MIB::ruckusSZAPRebootTrap
RUCKUS-SZ-EVENT-MIB::ruckusSZEventSeverity.0 \"Critical\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventCode.0 \"301\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventType.0 \"apRebootByUser\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName.0 \"$device->hostname\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPMacAddr.0 \"de:ad:be:ef:33:40\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP.0 \"$device->ip\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPLocation.0 \"$device->location\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPDescription.0 \"$device->sysDescr\"
RUCKUS-SZ-EVENT-MIB::ruckusSZEventReason.0 \"AP rebooted by controller user\"";

        $trap = new Trap($trapText);

        $message = "AP at site $device->location rebooted with reason AP rebooted by controller user";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ruckusSZAPRebootTrap');
    }
}
