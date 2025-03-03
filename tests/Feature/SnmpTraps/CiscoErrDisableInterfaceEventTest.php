<?php
/**
 * CiscoErrDisableInterfaceEventTest.php
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
 * @copyright  2022 Andy Norwood
 * @author     Andy Norwood(bonzo81))
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class CiscoErrDisableInterfaceEventTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    /**
     * Test CiscoErrDisableInterfaceEvent trap handle
     *
     * @return void
     */
    public function testErrDisableInterfaceEvent(): void
    {
        $device = Device::factory()->create();
        /** @var Device $device */
        $port = Port::factory()->make();
        /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 CISCO-ERR-DISABLE-MIB::cErrDisableInterfaceEventRev1
CISCO-ERR-DISABLE-MIB::cErrDisableIfStatusCause.$port->ifIndex.0 bpduGuard",
            'SNMP TRAP: bpduGuard error detected on ' . $port->ifName . ' (Description: ' . $port->ifDescr . '). ' . $port->ifName . ' in err-disable state.',
            'Could not handle testErrDisableInterfaceEvent trap',
            [Severity::Warning],
            $device,
        );
    }

    /**
     * Test CiscoErrDisableBadIfIndex trap handle
     *
     * @return void
     */
    public function testErrDisableBadIfIndex(): void
    {
        $device = Device::factory()->create();
        /** @var Device $device */
        $port = Port::factory()->make(['ifIndex' => 1]);
        /** @var Port $port */
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 CISCO-ERR-DISABLE-MIB::cErrDisableInterfaceEventRev1
CISCO-ERR-DISABLE-MIB::cErrDisableIfStatusCause.10.0 bpduGuard",
            'SNMP TRAP: bpduGuard error detected on unknown port. Either ifIndex is not found in the trap, or it does not match a port on this device.',
            'Could not handle testErrDisableBadIfIndex trap',
            [Severity::Warning],
            $device,
        );
    }
}
