<?php
/**
 * HpicfBridgeLoopProtectLoopDetectedNotificationTest.php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class HpicfBridgeLoopProtectLoopDetectedNotificationTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    /**
     * Test HpicfBridgeLoopProtectLoopDetectedNotification.php handler
     *
     * @return void
     */
    public function testHpicfBridgeLoopProtectLoopDetectedNotification(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make(['ifIndex' => '1', 'ifDescr' => 'A1']);
        $device->ports()->save($port);

        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:44289->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 82:19:24:56.09
SNMPv2-MIB::snmpTrapOID.0 HP-ICF-BRIDGE::hpicfBridgeLoopProtectLoopDetectedNotification
IF-MIB::ifIndex.$port->ifIndex $port->ifIndex
HP-ICF-BRIDGE::hpicfBridgeLoopProtectPortLoopCount 1
HP-ICF-BRIDGE::hpicfBridgeLoopProtectPortReceiverAction disableTx",
            "Loop Detected $port->ifDescr (Count 1, Action disableTx)",
            'Could not handle HP-ICF-BRIDGE::HpicfBridgeLoopProtectLoopDetectedNotification trap',
            [Severity::Warning, 'loop', $port->ifDescr],
            $device
        );
    }
}
