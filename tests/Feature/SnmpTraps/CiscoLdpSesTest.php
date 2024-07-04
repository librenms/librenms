<?php
/**
 * CiscoLdpSesTest.php
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
 *
 * Tests CiscoLdpSesDown and CiscoLdpSesUp traps from Cisco devices.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Olivier M.
 * @author     Olivier MORFIN <morfin.olivier@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class CiscoLdpSesTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    public function testCiscoLdpSesDownTrap(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up', 'ifDescr' => 'GigabitEthernet0/1', 'ifAlias' => 'test']);
        $device->ports()->save($port);
        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:64610->[127.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 17:58:59.10
SNMPv2-MIB::snmpTrapOID.0 MPLS-LDP-MIB::mplsLdpSessionDown
MPLS-LDP-MIB::mplsLdpEntityPeerObjects.4.1.1.78.41.184.3.0.0.1311357842.78.41.184.1.0.0 = INTEGER: 1
IF-MIB::ifIndex $port->ifIndex",
            "LDP session DOWN on interface $port->ifDescr - $port->ifAlias",
            'Could not handle ciscoLdpSesDown trap',
            [Severity::Warning, 'interface', $port->port_id],
            $device,
        );
    }

    public function testCiscoLdpSesUpTrap(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up', 'ifDescr' => 'GigabitEthernet0/1', 'ifAlias' => 'test']);
        $device->ports()->save($port);
        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:64610->[127.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 17:58:59.10
SNMPv2-MIB::snmpTrapOID.0 MPLS-LDP-MIB::mplsLdpSessionUp
MPLS-LDP-MIB::mplsLdpEntityPeerObjects.4.1.1.78.41.184.3.0.0.1311357842.78.41.184.1.0.0 = INTEGER: 5
IF-MIB::ifIndex $port->ifIndex",
            "LDP session UP on interface $port->ifDescr - $port->ifAlias",
            'Could not handle CiscoLdpSesUp trap',
            [Severity::Ok, 'interface', $port->port_id],
            $device,
        );
    }
}
