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
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']); /** @var Port $port */
        $device->ports()->save($port);

        $warning = "Snmptrap LdpSesDown: Could not find port at ifIndex $port->ifIndex for device: $device->hostname";
        \Log::shouldReceive('warning')->never()->with($warning);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:64610->[127.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 17:58:59.10
SNMPv2-MIB::snmpTrapOID.0 MPLS-LDP-MIB::mplsLdpSessionDown
MPLS-LDP-MIB::mplsLdpEntityPeerObjects.4.1.1.78.41.184.3.0.0.1311357842.78.41.184.1.0.0 = INTEGER: 1
IF-MIB::ifIndex.0 $port->ifIndex",
            "LDP session on interface $port->ifDescr is down",
            'Could not handle CiscoLdpSesDown trap',
            [Severity::Warning],
            $device,
        );
    }

    public function testCiscoLdpSesUpTrap(): void
    {
        $device = Device::factory()->create();
        /** @var Device $device */
        $port = Port::factory()->make(['ifAdminStatus' => 'up', 'ifOperStatus' => 'up']);
        /** @var Port $port */
        $device->ports()->save($port);

        $warning = "Snmptrap LdpSesUp: Could not find port at ifIndex $port->ifIndex for device: $device->hostname";
        \Log::shouldReceive('warning')->never()->with($warning);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:64610->[127.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 17:58:59.10
SNMPv2-MIB::snmpTrapOID.0 MPLS-LDP-MIB::mplsLdpSessionDown
MPLS-LDP-MIB::mplsLdpEntityPeerObjects.4.1.1.78.41.184.3.0.0.1311357842.78.41.184.1.0.0 = INTEGER: 5
IF-MIB::ifIndex.0 $port->ifIndex",
            "LDP session on interface $port->ifDescr is up",
            'Could not handle CiscoLdpSesUp trap',
            [Severity::Ok],
            $device,
        );
    }
}
