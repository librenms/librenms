<?php
/**
 * PermissionsUtilTest.php
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

use Illuminate\Support\Collection;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\LaravelTestCase;
use LibreNMS\Util\Permissions;

class PermissionsUtilTest extends LaravelTestCase
{
    public function testGetPermissions()
    {
        $expected = new Collection([
            'devices' => new Collection([
                new Collection(['user_id' => 3, 'device_id' => 2]),
                new Collection(['user_id' => 4, 'device_id' => 5]),
                new Collection(['user_id' => 99, 'device_id' => 5]),
            ]),
            'ports' => new Collection([
                new Collection(['user_id' => 3, 'port_id' => 2]),
                new Collection(['user_id' => 3, 'port_id' => 3]),
                new Collection(['user_id' => 6, 'port_id' => 7]),
            ]),
            'bills' => new Collection([
                new Collection(['user_id' => 3, 'bill_id' => 2]),
                new Collection(['user_id' => 53, 'bill_id' => 4]),
                new Collection(['user_id' => 2, 'bill_id' => 6]),
                new Collection(['user_id' => 5, 'bill_id' => 2]),
            ]),
        ]);
        \DB::table('devices_perms')->insert($expected->get('devices')->toArray());
        \DB::table('ports_perms')->insert($expected->get('ports')->toArray());
        \DB::table('bill_perms')->insert($expected->get('bills')->toArray());

        $permissions = new Permissions();

        $this->assertEquals($expected, $permissions->getPermissions());
    }
}
