<?php
/*
 * FgTrapAvOversizeTest.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Unit tests for Fortigate FgTrapAvOversized.php
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\DBTestCase;

class FgTrapAvOversizeTest extends SnmpTrapTestCase
{
    public function testAvOversize()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapAvOversize
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname";

        $message = "$device->hostname received a file that exceeds proxy buffer, skipping AV scan";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapIpsAvOversize');
    }
}
