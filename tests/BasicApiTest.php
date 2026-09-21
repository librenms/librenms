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

use App\Models\AlertRule;
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
        $token = $user->createToken('test');
        $device = Device::factory()->create();

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'devices' => [$device->toArray()],
                'count' => 1,
            ]);
    }

    public function testDisabledUserTokenCannotAccessApi(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create(['enabled' => false]);
        $token = $user->createToken('test');

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(401);
    }

    public function testDisabledTokenCannotAccessApi(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $token->accessToken->expires_at = now()->subDay();
        $token->accessToken->save();

        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(401);
    }

    public function testPrefixedAndUnprefixedTokensBothAuthenticate(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $device = Device::factory()->create();

        [$id, $secret] = explode('|', $token->plainTextToken, 2);

        // Test with X-Auth-Token (ID prefix)
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        auth()->forgetGuards();

        // Test with X-Auth-Token (without ID prefix)
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $secret])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        auth()->forgetGuards();

        // Test with Bearer token
        $this->json('GET', '/api/v0/devices', [], ['Authorization' => "Bearer {$token->plainTextToken}"])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        auth()->forgetGuards();

        // Test with query parameter api_token
        $this->json('GET', "/api/v0/devices?api_token={$token->plainTextToken}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        auth()->forgetGuards();

        // Test with invalid ID prefix
        $mismatchedId = ((int) $id) + 999;
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => "{$mismatchedId}|{$secret}"])
            ->assertStatus(401);
    }

    public function testMigratedLegacyTokensAuthenticate(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $legacyRawToken = bin2hex(random_bytes(16)); // 32-char hex legacy token

        $token = $user->tokens()->create([
            'name' => 'Legacy Token',
            'token' => hash('sha256', $legacyRawToken), // as migrated
            'abilities' => ['*'],
        ]);

        // Legacy unprefixed token continues to authenticate via X-Auth-Token
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $legacyRawToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        // Prefixed with ID also authenticates
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => "{$token->id}|{$legacyRawToken}"])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testRotateTokenInvalidatesOldTokenAndGeneratesNewPrefixedToken(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $oldPlainToken = $token->plainTextToken;

        $token->accessToken->delete();
        $newToken = $user->createToken('test');
        $newPlainToken = $newToken->plainTextToken;

        $this->assertNotSame($oldPlainToken, $newPlainToken);
        $this->assertStringStartsWith("{$newToken->accessToken->id}|", $newPlainToken);

        // Old token no longer valid
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $oldPlainToken])
            ->assertStatus(401);

        // New token is valid
        $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $newPlainToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testGetDeviceWirelessSensors(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
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

        $response = $this->json('GET', "/api/v0/devices/{$device->device_id}/wireless-sensors", [], ['X-Auth-Token' => $token->plainTextToken]);

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
        $token = $user->createToken('test');
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
            ['X-Auth-Token' => $token->plainTextToken]
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
        $token = $user->createToken('test');
        $device = Device::factory()->create();

        $this->json(
            'GET',
            "/api/v0/devices/{$device->device_id}/wireless-sensors?class=bogus",
            [],
            ['X-Auth-Token' => $token->plainTextToken]
        )->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => "Invalid wireless sensor class 'bogus'",
            ]);
    }

    public function testV1OnlyAcceptsBearerToken(): void
    {
        \App\Facades\LibrenmsConfig::set('api.v1.enabled', true);

        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');

        // Bearer token should authenticate on v1
        $this->json('GET', '/api/v1/system', [], ['Authorization' => "Bearer {$token->plainTextToken}"])
            ->assertStatus(200);

        auth()->forgetGuards();

        // X-Auth-Token should be rejected on v1
        $this->json('GET', '/api/v1/system', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(401);

        auth()->forgetGuards();

        // Query parameter api_token should be rejected on v1
        $this->json('GET', "/api/v1/system?api_token={$token->plainTextToken}")
            ->assertStatus(401);
    }

    public function testAddRuleStoresProcedureUrl(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');

        $this->json('POST', '/api/v0/rules', [
            'devices' => ['-1'],
            'name' => 'proc rule',
            'severity' => 'critical',
            'proc' => 'https://example.org/runbook',
            'builder' => self::alertRuleBuilder(),
        ], ['X-Auth-Token' => $token->plainTextToken])->assertStatus(200);

        $this->assertSame(
            'https://example.org/runbook',
            AlertRule::query()->where('name', 'proc rule')->value('proc')
        );
    }

    public function testEditRuleUpdatesProcedureUrl(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $rule = AlertRule::factory()->create(['proc' => 'https://example.org/old']);

        $this->json('PUT', '/api/v0/rules', [
            'rule_id' => $rule->id,
            'devices' => ['-1'],
            'name' => $rule->name,
            'severity' => 'critical',
            'proc' => 'https://example.org/new',
            'builder' => self::alertRuleBuilder(),
        ], ['X-Auth-Token' => $token->plainTextToken])->assertStatus(200);

        $this->assertSame('https://example.org/new', $rule->fresh()->proc);
    }

    public function testEditRuleLeavesProcedureUrlAloneWhenOmitted(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $rule = AlertRule::factory()->create(['proc' => 'https://example.org/keep']);

        $this->json('PUT', '/api/v0/rules', [
            'rule_id' => $rule->id,
            'devices' => ['-1'],
            'name' => $rule->name,
            'severity' => 'critical',
            'builder' => self::alertRuleBuilder(),
        ], ['X-Auth-Token' => $token->plainTextToken])->assertStatus(200);

        $this->assertSame('https://example.org/keep', $rule->fresh()->proc);
    }

    /**
     * @return array<string, mixed>
     */
    private static function alertRuleBuilder(): array
    {
        return [
            'condition' => 'AND',
            'rules' => [[
                'id' => 'devices.hostname',
                'field' => 'devices.hostname',
                'type' => 'string',
                'input' => 'text',
                'operator' => 'equal',
                'value' => 'localhost',
            ]],
            'valid' => true,
        ];
    }
}
