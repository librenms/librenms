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
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use App\Models\Vminfo;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;

final class BasicApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testListDevices(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $device = Device::factory()->create();

        $res = $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1);

        $deviceData = $res->json('devices.0');
        $this->assertIsArray($deviceData);
        $this->assertArrayNotHasKey('parents', $deviceData);
        $this->assertSame($device->hostname, $deviceData['hostname']);
        $this->assertSame((int) $device->status, (int) $deviceData['status']);
        $this->assertIsInt($deviceData['status']);
    }

    public function testListDevicesWithParentsShape(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');

        $parent1 = Device::factory()->create(['hostname' => 'parent1.example.com']);
        $parent2 = Device::factory()->create(['hostname' => 'parent2.example.com']);
        $child = Device::factory()->create(['hostname' => 'child.example.com']);

        $child->parents()->attach([$parent1->device_id, $parent2->device_id]);

        $res = $this->json('GET', '/api/v0/devices?type=hostname&query=child', [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1);

        $childData = $res->json('devices.0');
        $this->assertArrayNotHasKey('parents', $childData);
        $this->assertStringContainsString((string) $parent1->device_id, (string) $childData['dependency_parent_id']);
        $this->assertStringContainsString((string) $parent2->device_id, (string) $childData['dependency_parent_id']);
        $this->assertStringContainsString('parent1.example.com', (string) $childData['dependency_parent_hostname']);
        $this->assertStringContainsString('parent2.example.com', (string) $childData['dependency_parent_hostname']);
    }

    #[DataProvider('serviceListTimingProvider')]
    public function testListServicesIncludesCheckTiming(bool $deviceOnly): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $device = Device::factory()->create();
        $checkedAt = 1787468400;
        $service = Service::factory()->for($device)->create([
            'service_checked' => $checkedAt,
            'service_name' => 'HTTP availability',
            'service_type' => 'http',
            'service_desc' => 'Public web endpoint',
            'service_status' => 2,
            'service_message' => 'HTTP CRITICAL',
        ]);
        $neverChecked = Service::factory()->for($device)->create();

        $url = $deviceOnly ? "/api/v0/services/{$device->device_id}" : '/api/v0/services';
        $response = $this->json('GET', $url, [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonCount(2, 'services.0');

        $services = array_column($response->json('services.0'), null, 'service_id');
        $result = $services[$service->service_id];
        $this->assertSame($checkedAt, $result['service_checked']);
        $this->assertSame(0, $services[$neverChecked->service_id]['service_checked']);
        $this->assertSame($device->device_id, (int) $result['device_id']);
        $this->assertSame('HTTP availability', $result['service_name']);
        $this->assertSame('http', $result['service_type']);
        $this->assertSame('Public web endpoint', $result['service_desc']);
        $this->assertSame(2, (int) $result['service_status']);
        $this->assertSame('HTTP CRITICAL', $result['service_message']);
        $this->assertArrayNotHasKey('service_check_interval', $result);
    }

    public static function serviceListTimingProvider(): array
    {
        return [
            'all services' => [false],
            'device services' => [true],
        ];
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

    public function testGetDevice(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        $location = Location::factory()->create(['location' => 'Server Room A', 'lat' => 10.5, 'lng' => 20.5]);
        $device = Device::factory()->create([
            'hostname' => 'test-device-1.domain.local',
            'location_id' => $location->id,
        ]);
        $vmHost = Device::factory()->create(['hostname' => 'esxi-host.domain.local']);
        Vminfo::factory()->create([
            'device_id' => $vmHost->device_id,
            'vmwVmDisplayName' => $device->hostname,
        ]);

        // Get by hostname
        $response = $this->json('GET', "/api/v0/devices/{$device->hostname}", [], ['X-Auth-Token' => $token->plainTextToken]);
        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.hostname', $device->hostname)
            ->assertJsonPath('devices.0.location', 'Server Room A')
            ->assertJsonPath('devices.0.parent_id', $vmHost->device_id);

        $getDeviceData = $response->json('devices.0');
        $this->assertIsArray($getDeviceData);
        $this->assertArrayNotHasKey('parents', $getDeviceData);
        $this->assertSame((int) $device->status, (int) $getDeviceData['status']);
        $this->assertIsInt($getDeviceData['status']);

        // Get by device_id
        $this->json('GET', "/api/v0/devices/{$device->device_id}", [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('devices.0.device_id', $device->device_id);

        // Nonexistent device
        $this->json('GET', '/api/v0/devices/nonexistent-device.local', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(404);
    }

    public function testGetDeviceUnauthorized(): void
    {
        $device = Device::factory()->create(['hostname' => 'unauthorized-device.domain.local']);

        /** @var User $normalUser */
        $normalUser = User::factory()->create();
        $normalToken = $normalUser->createToken('normal');

        $this->json('GET', "/api/v0/devices/{$device->device_id}", [], ['X-Auth-Token' => $normalToken->plainTextToken])
            ->assertStatus(403);
    }

    public function testListDevicesFilters(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        Device::factory()->create([
            'hostname' => 'alpha.domain.local',
            'os' => 'linux',
            'status' => 1,
            'ignore' => 0,
            'disabled' => 0,
        ]);
        Device::factory()->create([
            'hostname' => 'beta.domain.local',
            'os' => 'cisco',
            'status' => 0,
            'ignore' => 0,
            'disabled' => 1,
        ]);

        // Filter by os
        $res = $this->json('GET', '/api/v0/devices?type=os&query=linux', [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.hostname', 'alpha.domain.local');

        // Filter by disabled
        $res = $this->json('GET', '/api/v0/devices?type=disabled', [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.hostname', 'beta.domain.local');

        // Filter by up
        $res = $this->json('GET', '/api/v0/devices?type=up', [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.hostname', 'alpha.domain.local');
    }

    public function testListDevicesNormalUserPermissions(): void
    {
        $device1 = Device::factory()->create(['hostname' => 'alpha.domain.local']);
        Device::factory()->create(['hostname' => 'beta.domain.local']);

        /** @var User $normalUser */
        $normalUser = User::factory()->create();
        $normalUser->assignRole('user');
        $normalUser->devicesOwned()->attach($device1->device_id);
        \App\Facades\Permissions::invalidateCache();
        $normalToken = $normalUser->createToken('normal');

        $res = $this->json('GET', '/api/v0/devices', [], ['X-Auth-Token' => $normalToken->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.hostname', 'alpha.domain.local');
    }

    public function testAddDeviceValidationAndCreation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        // Empty body
        $this->json('POST', '/api/v0/devices', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(400);

        // Missing hostname
        $this->json('POST', '/api/v0/devices', ['snmp_disable' => 1], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(400);

        // Invalid hostname
        $this->json('POST', '/api/v0/devices', ['hostname' => 'invalid hostname!'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(400);

        // Successful ping device creation
        $res = $this->json('POST', '/api/v0/devices', [
            'hostname' => 'ping-host.test.local',
            'snmp_disable' => 1,
            'force_add' => 1,
            'os' => 'ping',
            'location' => 'Lab 1',
        ], ['X-Auth-Token' => $token->plainTextToken]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $addedDeviceData = $res->json('devices.0');
        $this->assertIsArray($addedDeviceData);
        $this->assertArrayNotHasKey('parents', $addedDeviceData);
        $this->assertSame(1, (int) $addedDeviceData['snmp_disable']);
        $this->assertSame('ping-host.test.local', $addedDeviceData['hostname']);
        $this->assertSame('ping', $addedDeviceData['os']);

        $this->assertDatabaseHas('devices', ['hostname' => 'ping-host.test.local', 'os' => 'ping', 'snmp_disable' => 1]);
    }

    public function testDelDevice(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        $device = Device::factory()->create(['hostname' => 'delete-me.domain.local']);

        // Nonexistent
        $this->json('DELETE', '/api/v0/devices/unknown.local', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(404);

        // Delete by hostname
        $res = $this->json('DELETE', "/api/v0/devices/{$device->hostname}", [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('devices.0.hostname', 'delete-me.domain.local');

        $deletedDeviceData = $res->json('devices.0');
        $this->assertIsArray($deletedDeviceData);
        $this->assertArrayNotHasKey('parents', $deletedDeviceData);

        $this->assertDatabaseMissing('devices', ['device_id' => $device->device_id]);
    }

    public function testUpdateDevice(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        $device = Device::factory()->create([
            'hostname' => 'update-target.domain.local',
            'sysName' => 'Original Name',
        ]);

        // Nonexistent
        $this->json('PATCH', '/api/v0/devices/unknown.local', ['field' => 'sysName', 'data' => 'New'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(404);

        // Missing field
        $this->json('PATCH', "/api/v0/devices/{$device->device_id}", [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(400);

        // Disallowed field
        $this->json('PATCH', "/api/v0/devices/{$device->device_id}", ['field' => 'hostname', 'data' => 'new.local'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(500);

        // Single field update
        $res = $this->json('PATCH', "/api/v0/devices/{$device->device_id}", ['field' => 'sysName', 'data' => 'Updated Switch'], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok');
        $this->assertSame('updated switch', $device->fresh()->sysName);

        // Update location field
        $res = $this->json('PATCH', "/api/v0/devices/{$device->device_id}", ['field' => 'location', 'data' => 'Datacenter 2'], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok');
        $this->assertSame('Datacenter 2', $device->fresh()->location->location);

        // Multi field update
        $res = $this->json('PATCH', "/api/v0/devices/{$device->device_id}", [
            'field' => ['sysName', 'purpose'],
            'data' => ['Core Switch', 'Production Core'],
        ], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok');
        $this->assertSame('core switch', $device->fresh()->sysName);
        $this->assertSame('Production Core', $device->fresh()->purpose);
    }

    public function testGetDevicesByGroup(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');

        $group = DeviceGroup::factory()->create(['name' => 'Edge Routers']);
        $device = Device::factory()->create(['hostname' => 'edge1.domain.local']);

        // Group not found
        $this->json('GET', '/api/v0/devicegroups/NonexistentGroup', [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(404);

        // Empty group
        $this->json('GET', "/api/v0/devicegroups/{$group->name}", [], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(404);

        // Attach device to group
        $group->devices()->attach($device);

        // Get devices by group name
        $res = $this->json('GET', "/api/v0/devicegroups/{$group->name}", [], ['X-Auth-Token' => $token->plainTextToken]);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('devices.0.device_id', $device->device_id);

        // Get devices by group ID with full=1
        $resFull = $this->json('GET', "/api/v0/devicegroups/{$group->id}?full=1", [], ['X-Auth-Token' => $token->plainTextToken]);
        $resFull->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('devices.0.hostname', 'edge1.domain.local');
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
