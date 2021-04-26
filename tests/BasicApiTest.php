<?php
/**
 * BasicApiTest.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Models\ApiToken;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BasicApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testListDevices()
    {
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'devices' => [$device->toArray()],
                'count'=> 1,
            ]);
    }
}
