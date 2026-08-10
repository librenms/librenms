<?php

/**
 * CiscoSBRlDot1dStpPortStateForwardingTest.php
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
 * @copyright  2024 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('CiscoSB rldot1dStpPortStateForwarding Trap')]
final class CiscoSBRlDot1dStpPortStateForwardingTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    #[TestDox('CiscoSB rldot1dStpPortStateForwarding')]
    public function testCiscoSBRlDot1dStpPortStateForwarding(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifDescr' => 'gi5']);
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:49563->[10.0.0.1]:162
DISMAN-EXPRESSION-MIB::sysUpTimeInstance 12 days, 8:18:35.35
SNMPv2-MIB::snmpTrapOID.0 CISCOSB-TRAPS-MIB::rldot1dStpPortStateForwarding
CISCOSB-BRIDGEMIBOBJECTS-MIB::rldot1dStpTrapVrblifIndex.$port->ifIndex $port->ifIndex
CISCOSB-DEVICEPARAMS-MIB::rndErrorDesc.0 %STP-W-PORTSTATUS: gi5: STP status Forwarding",
            'CISCOSB Bridge Port STP State: Interface gi5 STP status transitioned from Learning to Forwarding State',
            'Could not handle CiscoSBRlDot1dStpPortStateForwarding trap',
            [Severity::Info],
            $device,
        );
    }
}
