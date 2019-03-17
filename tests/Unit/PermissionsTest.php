<?php
/**
 * PermissionsTest.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use LibreNMS\Tests\LaravelTestCase;
use Mockery\Mock;

class PermissionsTest extends LaravelTestCase
{
    public function testUserCanAccessDevice()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getDevicePermissions')->andReturn(collect([
            (object)['user_id' => 43, 'device_id' => 54],
            (object)['user_id' => 43, 'device_id' => 32],
            (object)['user_id' => 14, 'device_id' => 54],
        ]));

        $device = factory(Device::class)->make(['device_id' => 54]);
        $user = factory(User::class)->make(['user_id' => 43]);
        $this->assertTrue($perms->canAccessDevice($device, 43));
        $this->assertTrue($perms->canAccessDevice($device, $user));
        $this->assertTrue($perms->canAccessDevice(54, $user));
        $this->assertTrue($perms->canAccessDevice(54, 43));
        $this->assertTrue($perms->canAccessDevice(54, 43));
        $this->assertFalse($perms->canAccessDevice(54, 23));
        $this->assertFalse($perms->canAccessDevice(23, 43));
        $this->assertFalse($perms->canAccessDevice(54));

        \Auth::shouldReceive('id')->once()->andReturn(43);
        $this->assertTrue($perms->canAccessDevice(54));
        \Auth::shouldReceive('id')->once()->andReturn(23);
        $this->assertFalse($perms->canAccessDevice(54));
    }

    public function testDevicesForUser()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getDevicePermissions')->andReturn(collect([
            (object)['user_id' => 3, 'device_id' => 7],
            (object)['user_id' => 3, 'device_id' => 2],
            (object)['user_id' => 4, 'device_id' => 5],
        ]));

        $this->assertEquals(collect([7,2]), $perms->devicesForUser(3));
        $user = factory(User::class)->make(['user_id' => 3]);
        $this->assertEquals(collect([7,2]), $perms->devicesForUser($user));
        $this->assertEmpty($perms->devicesForUser(9));
        $this->assertEquals(collect(), $perms->devicesForUser());
        \Auth::shouldReceive('id')->once()->andReturn(4);
        $this->assertEquals(collect([5]), $perms->devicesForUser());
    }

    public function testUsersForDevice()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getDevicePermissions')->andReturn(collect([
            (object)['user_id' => 3, 'device_id' => 7],
            (object)['user_id' => 3, 'device_id' => 2],
            (object)['user_id' => 4, 'device_id' => 5],
            (object)['user_id' => 6, 'device_id' => 5],
        ]));

        $this->assertEquals(collect([4, 6]), $perms->usersForDevice(5));
        $this->assertEquals(collect([3]), $perms->usersForDevice(factory(Device::class)->make(['device_id' => 7])));
        $this->assertEquals(collect(), $perms->usersForDevice(6));
        $this->assertEmpty($perms->usersForDevice(9));
    }

    public function testUserCanAccessPort()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getPortPermissions')->andReturn(collect([
            (object)['user_id' => 43, 'port_id' => 54],
            (object)['user_id' => 43, 'port_id' => 32],
            (object)['user_id' => 14, 'port_id' => 54],
        ]));

        $port = factory(Port::class)->make(['port_id' => 54]);
        $user = factory(User::class)->make(['user_id' => 43]);
        $this->assertTrue($perms->canAccessPort($port, 43));
        $this->assertTrue($perms->canAccessPort($port, $user));
        $this->assertTrue($perms->canAccessPort(54, $user));
        $this->assertTrue($perms->canAccessPort(54, 43));
        $this->assertTrue($perms->canAccessPort(54, 43));
        $this->assertFalse($perms->canAccessPort(54, 23));
        $this->assertFalse($perms->canAccessPort(23, 43));
        $this->assertFalse($perms->canAccessPort(54));

        \Auth::shouldReceive('id')->once()->andReturn(43);
        $this->assertTrue($perms->canAccessPort(54));
        \Auth::shouldReceive('id')->once()->andReturn(23);
        $this->assertFalse($perms->canAccessPort(54));
    }

    public function testPortsForUser()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getPortPermissions')->andReturn(collect([
            (object)['user_id' => 3, 'port_id' => 7],
            (object)['user_id' => 3, 'port_id' => 2],
            (object)['user_id' => 4, 'port_id' => 5],
        ]));

        $this->assertEquals(collect([7,2]), $perms->portsForUser(3));
        $user = factory(User::class)->make(['user_id' => 3]);
        $this->assertEquals(collect([7,2]), $perms->portsForUser($user));
        $this->assertEmpty($perms->portsForUser(9));
        $this->assertEquals(collect(), $perms->portsForUser());
        \Auth::shouldReceive('id')->once()->andReturn(4);
        $this->assertEquals(collect([5]), $perms->portsForUser());
    }

    public function testUsersForPort()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getPortPermissions')->andReturn(collect([
            (object)['user_id' => 3, 'port_id' => 7],
            (object)['user_id' => 3, 'port_id' => 2],
            (object)['user_id' => 4, 'port_id' => 5],
            (object)['user_id' => 6, 'port_id' => 5],
        ]));

        $this->assertEquals(collect([4, 6]), $perms->usersForPort(5));
        $this->assertEquals(collect([3]), $perms->usersForPort(factory(Port::class)->make(['port_id' => 7])));
        $this->assertEquals(collect(), $perms->usersForPort(6));
        $this->assertEmpty($perms->usersForPort(9));
    }

    public function testUserCanAccessBill()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getBillPermissions')->andReturn(collect([
            (object)['user_id' => 43, 'bill_id' => 54],
            (object)['user_id' => 43, 'bill_id' => 32],
            (object)['user_id' => 14, 'bill_id' => 54],
        ]));

        $bill = factory(Bill::class)->make(['bill_id' => 54]);
        $user = factory(User::class)->make(['user_id' => 43]);
        $this->assertTrue($perms->canAccessBill($bill, 43));
        $this->assertTrue($perms->canAccessBill($bill, $user));
        $this->assertTrue($perms->canAccessBill(54, $user));
        $this->assertTrue($perms->canAccessBill(54, 43));
        $this->assertTrue($perms->canAccessBill(54, 43));
        $this->assertFalse($perms->canAccessBill(54, 23));
        $this->assertFalse($perms->canAccessBill(23, 43));
        $this->assertFalse($perms->canAccessBill(54));

        \Auth::shouldReceive('id')->once()->andReturn(43);
        $this->assertTrue($perms->canAccessBill(54));
        \Auth::shouldReceive('id')->once()->andReturn(23);
        $this->assertFalse($perms->canAccessBill(54));
    }

    public function testBillsForUser()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getBillPermissions')->andReturn(collect([
            (object)['user_id' => 3, 'bill_id' => 7],
            (object)['user_id' => 3, 'bill_id' => 2],
            (object)['user_id' => 4, 'bill_id' => 5],
        ]));

        $this->assertEquals(collect([7,2]), $perms->billsForUser(3));
        $user = factory(User::class)->make(['user_id' => 3]);
        $this->assertEquals(collect([7,2]), $perms->billsForUser($user));
        $this->assertEmpty($perms->billsForUser(9));
        $this->assertEquals(collect(), $perms->billsForUser());
        \Auth::shouldReceive('id')->once()->andReturn(4);
        $this->assertEquals(collect([5]), $perms->billsForUser());
    }

    public function testUsersForBill()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getBillPermissions')->andReturn(collect([
            (object)['user_id' => 3, 'bill_id' => 7],
            (object)['user_id' => 3, 'bill_id' => 2],
            (object)['user_id' => 4, 'bill_id' => 5],
            (object)['user_id' => 6, 'bill_id' => 5],
        ]));

        $this->assertEquals(collect([4, 6]), $perms->usersForBill(5));
        $this->assertEquals(collect([3]), $perms->usersForBill(factory(Bill::class)->make(['bill_id' => 7])));
        $this->assertEquals(collect(), $perms->usersForBill(6));
        $this->assertEmpty($perms->usersForBill(9));
    }
}
