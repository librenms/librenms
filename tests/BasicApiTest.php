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
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;

final class BasicApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testListDevices(): void
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

    public function testGetDeviceWirelessSensors(): void
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

    public function testGetDeviceWirelessSensorsSupportsFilteringAndColumns(): void
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

    public function testGetDeviceWirelessSensorsRejectsInvalidClass(): void
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
}
