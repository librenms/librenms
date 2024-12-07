<?php
/**
 * PortsTrapTest.php
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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class PortsTrapTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    public function testLinkDown(): void
    {
        // make a device and associate a port with it
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']); /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("<UNKNOWN>
UDP: [$device->ip]:57123->[192.168.4.4]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:12.87
SNMPv2-MIB::snmpTrapOID.0 IF-MIB::linkDown
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
IF-MIB::ifAdminStatus.$port->ifIndex down
IF-MIB::ifOperStatus.$port->ifIndex down
IF-MIB::ifDescr.$port->ifIndex GigabitEthernet0/5
IF-MIB::ifType.$port->ifIndex ethernetCsmacd
OLD-CISCO-INTERFACES-MIB::locIfReason.$port->ifIndex \"down\"\n",
            [
                'SNMP Trap: linkDown down/down ' . $port->ifDescr,
                "Interface Disabled : $port->ifDescr (TRAP)",
                "Interface went Down : $port->ifDescr (TRAP)",
            ],
            'Could not handle linkDown',
            [
                [Severity::Error, 'interface', $port->port_id],
                [Severity::Notice, 'interface', $port->port_id],
                [Severity::Error, 'interface', $port->port_id],
            ],
            $device,
        );

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'down');
        $this->assertEquals($port->ifOperStatus, 'down');
    }

    public function testLinkUp(): void
    {
        // make a device and associate a port with it
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'down', 'ifOperStatus' => 'down']); /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("<UNKNOWN>
UDP: [$device->ip]:57123->[185.29.68.52]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 2:15:07:18.21
SNMPv2-MIB::snmpTrapOID.0 IF-MIB::linkUp
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
IF-MIB::ifAdminStatus.$port->ifIndex up
IF-MIB::ifOperStatus.$port->ifIndex up
IF-MIB::ifDescr.$port->ifIndex GigabitEthernet0/5
IF-MIB::ifType.$port->ifIndex ethernetCsmacd
OLD-CISCO-INTERFACES-MIB::locIfReason.$port->ifIndex \"up\"\n",
            [
                'SNMP Trap: linkUp up/up ' . $port->ifDescr,
                "Interface Enabled : $port->ifDescr (TRAP)",
                "Interface went Up : $port->ifDescr (TRAP)",
            ],
            'Could not handle linkUp',
            [
                [Severity::Ok, 'interface', $port->port_id],
                [Severity::Notice, 'interface', $port->port_id],
                [Severity::Ok, 'interface', $port->port_id],
            ],
            $device,
        );

        $port = $port->fresh(); // refresh from database
        $this->assertEquals($port->ifAdminStatus, 'up');
        $this->assertEquals($port->ifOperStatus, 'up');
    }
}
