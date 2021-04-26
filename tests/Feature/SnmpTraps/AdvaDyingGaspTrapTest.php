<?php
/**
 * AdvaDyingGaspTrapTest.php
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
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class AdvaDyingGaspTrapTest extends SnmpTrapTestCase
{
    public function testDyingGasp()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmSnmpDyingGaspTrap";

        $trap = new Trap($trapText);

        $message = 'Dying Gasp received';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle cmSnmpDyingGaspTrap');
    }
}
