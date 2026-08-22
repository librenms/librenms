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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Models\ApiToken;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;

final class BasicApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_list_devices(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'devices' => [$device->toArray()],
                'count' => 1,
            ]);
    }

    public function test_disabled_user_token_cannot_access_api(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create(['enabled' => false]);
        $token = ApiToken::generateToken($user);

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(401);

        $this->assertFalse(ApiToken::isValid($token->token_hash));
        $this->assertNull(ApiToken::userFromToken($token->token_hash));
    }

    public function test_token_without_user_is_invalid(): void
    {
        $token = new ApiToken;
        $token->user_id = 999999;
        $token->token_hash = ApiToken::randomTokenValue();
        $token->description = 'Missing user';
        $token->disabled = false;
        $token->save();

        $this->assertFalse(ApiToken::isValid($token->token_hash));
        $this->assertNull(ApiToken::userFromToken($token->token_hash));
    }

    public function test_get_device_wireless_sensors(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();

        $rssi = WirelessSensor::factory()->for($device)->create([
            'sensor_class' => 'rssi',
            'sensor_index' => '1.1',
            'sensor_descr' => 'Subscriber 1 UL RSSI',
            'sensor_current' => -62,
        ]);
        WirelessSensor::factory()->for($device)->create([
            'sensor_class' => 'snr',
            'sensor_index' => '1.2',
            'sensor_descr' => 'Subscriber 1 UL SNR',
            'sensor_current' => 31,
        ]);
        WirelessSensor::factory()->for($device)->create([
            'sensor_class' => 'rssi',
            'sensor_index' => '1.3',
            'sensor_descr' => 'Deleted Sensor',
            'sensor_current' => 7,
            'sensor_deleted' => 1,
        ]);

        $response = $this->json('GET', "/api/v0/devices/{$device->device_id}/wireless-sensors", [], ['X-Auth-Token' => $token->token_hash]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 2)
            ->assertJsonCount(2, 'wireless_sensors');

        $this->assertSame($rssi->sensor_id, $response->json('wireless_sensors.0.sensor_id'));
        $this->assertSame('rssi', $response->json('wireless_sensors.0.sensor_class'));
        $this->assertSame('snr', $response->json('wireless_sensors.1.sensor_class'));
    }

    public function test_get_device_wireless_sensors_supports_filtering_and_columns(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();

        $rssi = WirelessSensor::factory()->for($device)->create([
            'sensor_class' => 'rssi',
            'sensor_index' => '2.1',
            'sensor_descr' => 'Subscriber 2 UL RSSI',
            'sensor_current' => -55,
        ]);
        WirelessSensor::factory()->for($device)->create([
            'sensor_class' => 'snr',
            'sensor_index' => '2.2',
            'sensor_descr' => 'Subscriber 2 UL SNR',
            'sensor_current' => 31,
        ]);

        $response = $this->json(
            'GET',
            "/api/v0/devices/{$device->device_id}/wireless-sensors?class=rssi&columns=sensor_id,sensor_class,sensor_descr,sensor_current,lastupdate",
            [],
            ['X-Auth-Token' => $token->token_hash]
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1)
            ->assertJsonCount(1, 'wireless_sensors');

        $row = $response->json('wireless_sensors.0');

        $this->assertSame($rssi->sensor_id, $row['sensor_id']);
        $this->assertSame('rssi', $row['sensor_class']);
        $this->assertSame('Subscriber 2 UL RSSI', $row['sensor_descr']);
        $this->assertEquals(-55, $row['sensor_current']);
        $this->assertArrayHasKey('lastupdate', $row);
        $this->assertArrayNotHasKey('sensor_type', $row);
    }

    public function test_get_device_wireless_sensors_rejects_invalid_class(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();

        $this->json(
            'GET',
            "/api/v0/devices/{$device->device_id}/wireless-sensors?class=bogus",
            [],
            ['X-Auth-Token' => $token->token_hash]
        )->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => "Invalid wireless sensor class 'bogus'",
            ]);
    }

    public function test_update_sensor_thresholds(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $sensor = Sensor::factory()->for($device)->create([
            'sensor_class' => 'temperature',
            'sensor_limit_low' => 5,
            'sensor_limit_low_warn' => 10,
            'sensor_limit_warn' => 70,
            'sensor_limit' => 80,
            'sensor_custom' => 'No',
        ]);

        $this->json('PATCH', "/api/v0/resources/sensors/{$sensor->sensor_id}", [
            'sensor_limit_warn' => 75,
            'sensor_limit' => 85,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('sensors.0.sensor_limit_warn', 75)
            ->assertJsonPath('sensors.0.sensor_limit', 85)
            ->assertJsonPath('sensors.0.sensor_custom', 'Yes');

        $sensor->refresh();
        $this->assertEquals(75, $sensor->sensor_limit_warn);
        $this->assertEquals(85, $sensor->sensor_limit);
        $this->assertSame('Yes', $sensor->sensor_custom);
    }

    public function test_bulk_update_sensor_thresholds(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $sensors = Sensor::factory()->count(2)->for($device)->create([
            'sensor_class' => 'temperature',
            'sensor_limit_low' => 5,
            'sensor_limit_low_warn' => 10,
            'sensor_limit_warn' => 70,
            'sensor_limit' => 80,
            'sensor_custom' => 'No',
        ]);

        $this->json('PATCH', '/api/v0/resources/sensors', [
            'sensor_ids' => $sensors->pluck('sensor_id')->all(),
            'sensor_limit_warn' => 72,
            'sensor_limit' => 82,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 2)
            ->assertJsonCount(2, 'sensors');

        foreach ($sensors as $sensor) {
            $sensor->refresh();
            $this->assertEquals(72, $sensor->sensor_limit_warn);
            $this->assertEquals(82, $sensor->sensor_limit);
            $this->assertSame('Yes', $sensor->sensor_custom);
        }
    }

    public function test_sensor_thresholds_reject_invalid_order(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $sensor = Sensor::factory()->for($device)->create([
            'sensor_class' => 'temperature',
            'sensor_limit_low' => 5,
            'sensor_limit_low_warn' => 10,
            'sensor_limit_warn' => 70,
            'sensor_limit' => 80,
            'sensor_custom' => 'No',
        ]);

        $this->json('PATCH', "/api/v0/resources/sensors/{$sensor->sensor_id}", [
            'sensor_limit_warn' => 90,
            'sensor_limit' => 85,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(422)
            ->assertJsonValidationErrors('sensor_limit');

        $sensor->refresh();
        $this->assertEquals(70, $sensor->sensor_limit_warn);
        $this->assertEquals(80, $sensor->sensor_limit);
        $this->assertSame('No', $sensor->sensor_custom);
    }

    public function test_read_only_user_cannot_update_sensor_thresholds(): void
    {
        /** @var User $user */
        $user = User::factory()->read()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $sensor = Sensor::factory()->for($device)->create([
            'sensor_class' => 'temperature',
            'sensor_limit_warn' => 70,
            'sensor_limit' => 80,
            'sensor_custom' => 'No',
        ]);

        $this->json('PATCH', "/api/v0/resources/sensors/{$sensor->sensor_id}", [
            'sensor_limit' => 85,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(403);

        $sensor->refresh();
        $this->assertEquals(80, $sensor->sensor_limit);
        $this->assertSame('No', $sensor->sensor_custom);
    }
}
