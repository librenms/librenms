<?php
/**
 * EdgeCosSuccessUserLoginTest.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\LaravelTestCase;

class EdgeCosSuccessUserLoginTest extends LaravelTestCase
{
    public function testManagedSeries()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
        UDP: [$device->ip]:44298->[192.168.5.5]:162
        DISMAN-EVENT-MIB::sysUpTimeInstance 0:6:11:31.55
        SNMPv2-MIB::snmpTrapOID.0 ES3528MO-MIB::swAuthenticationSuccess";

        $message = "SNMP Trap: Success User Login: {$device->displayName()}";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'auth', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ES3528MO-MIB::swAuthenticationSuccess trap');
    }

    public function testSmartSeries()
    {
        $device = factory(Device::class)->create();

        $trapText = "$device->hostname
        UDP: [$device->ip]:1026->[192.168.5.5]:162
        DISMAN-EVENT-MIB::sysUpTimeInstance 30:22:57:58.00
        SNMPv2-MIB::snmpTrapOID.0 ECS4210-MIB::swLoginSucceedTrap";
        
        $message = "SNMP Trap: Success User Login: {$device->displayName()}";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'auth', 3);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle ECS4210-MIB::swLoginSucceedTrap trap');
    }
}
