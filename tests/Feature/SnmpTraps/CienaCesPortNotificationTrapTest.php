<?php

/**
 * CienaCesPortNotificationTrapTest.php
 *
 * -Description-
 *
 * Test port up and down via Ciena's proprietary snmptraps.
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
 *
 * @copyright  2025 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class CienaCesPortNotificationTrapTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    public function testCienaCesPortDownNotification()
    {
        // make a device and associate a port with it
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']); /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57123->[192.168.4.4]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:12.87
SNMPv2-MIB::snmpTrapOID.0 CIENA-CES-PORT-MIB::cienaCesPortNotificationPortDown
CIENA-GLOBAL-MIB::cienaGlobalSeverity warning
CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingChassisIndex 1
CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingShelfIndex 1
CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingNotifSlotIndex 1
CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingNotifPortNumber $port->ifIndex
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortAdminState enabled
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortOperState disable
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortName $port->ifName
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortDesc $port->ifDescr",
            "Port down on Chassis: 1 Shelf: 1 Slot: 1 Port: $port->ifIndex",
            'Could not handle CienaCesPortDownNotification',
            [Severity::Error],
            $device,
        );

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifOperStatus, 'down');
    }
    public function testCienaCesPortUpNotification()
    {
        // make a device and associate a port with it
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']); /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57123->[192.168.4.4]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:12.87
SNMPv2-MIB::snmpTrapOID.0 CIENA-CES-PORT-MIB::cienaCesPortNotificationPortUp
CIENA-GLOBAL-MIB::cienaGlobalSeverity warning
CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingChassisIndex 1
CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingShelfIndex 1
CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingNotifSlotIndex 1
CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingNotifPortNumber $port->ifIndex
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortAdminState enabled
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortOperState enabled
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortName $port->ifName
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortType 1
CIENA-CES-PORT-MIB::cienaCesLogicalPortConfigPortDesc $port->ifDescr",
            "Port up on Chassis: 1 Shelf: 1 Slot: 1 Port: $port->ifIndex",
            'Could not handle CienaCesPortUpNotification',
            [Severity::Ok],
            $device,
        );

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifOperStatus, 'up');
    }
}