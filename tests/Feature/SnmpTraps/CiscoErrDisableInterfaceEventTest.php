<?php
/**
 * CiscoErr-Disable Interface Event.php
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
use App\Models\Port;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class CiscoErrDisableInterfaceEventTest extends SnmpTrapTestCase
{
    /**
     * Test CiscoMacViolation trap handle
     *
     * @return void
     */
    public function testErrDisableInterfaceEvent()
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(); /** @var Port $port */
        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[10.0.0.1]:162
SNMPv2-MIB::sysUpTime.0 18:30:30.32
SNMPv2-MIB::snmpTrapOID.0 CISCO-ERR-DISABLE-MIB::cErrDisableInterfaceEventRev1
CISCO-ERR-DISABLE-MIB::cErrDisableIfStatusCause.$port->ifIndex.0 bpduGuard";
        $trap = new Trap($trapText);
        $message = 'SNMP TRAP: bpduGuard error detected on ' . $port->ifName . ' (Description: ' . $port->ifDescr . '). ' . $port->ifName . ' in err-disable state.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle testErrDisableInterfaceEvent trap');
    }
}
