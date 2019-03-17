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

use App\Models\Device;
use App\Models\User;
use LibreNMS\Tests\LaravelTestCase;
use Mockery\Mock;

class PermissionsTest extends LaravelTestCase
{
    public function testUserCanAccessDevice()
    {
        $perms = \Mockery::mock(\LibreNMS\Permissions::class)->makePartial();
        $perms->shouldReceive('getDevicePermissions')->andReturn(collect([(object)['user_id' => 43, 'device_id' => 54]]));

        $device = new Device();
        $device->device_id = 54;
        $user = new User();
        $user->user_id = 43;
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
}
