<?php

/**
 * CieLinkUpTest.php
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
 * @copyright  2026 Neil Lathwood
 * @author     Neil Lathwood <neil@configuration.co.uk>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Cisco cieLinkUp Trap')]
final class CieLinkUpTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    #[TestDox('Cisco cieLinkUp')]
    public function testCieLinkUp(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifAdminStatus' => 'down', 'ifOperStatus' => 'down', 'ifDescr' => 'Ethernet1/42']);
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:49563->[10.0.0.1]:162
DISMAN-EXPRESSION-MIB::sysUpTimeInstance = Timeticks: (494617942) 57 days, 5:56:19.42
SNMPv2-MIB::snmpTrapOID.0 CISCO-IF-EXTENSION-MIB::cieLinkUp
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
IF-MIB::ifAdminStatus.$port->ifIndex up
IF-MIB::ifOperStatus.$port->ifIndex up
IF-MIB::ifName.$port->ifIndex Ethernet1/42
IF-MIB::ifType.$port->ifIndex ethernetCsmacd",
            [
                "Cisco cieLinkUp Trap: $port->ifDescr AdminStatus: up, OperStatus: up",
                "Interface Enabled : $port->ifDescr (TRAP)",
                "Interface went Up : $port->ifDescr (TRAP)",
            ],
            'Could not handle CieLinkUp trap',
            [
                [Severity::Ok, 'interface', $port->port_id],
                [Severity::Notice, 'interface', $port->port_id],
                [Severity::Ok, 'interface', $port->port_id],
            ],
            $device,
        );

        $port = $port->fresh();
        $this->assertEquals('up', $port->ifAdminStatus);
        $this->assertEquals('up', $port->ifOperStatus);
    }
}
