<?php

/**
 * DeviceConfigTabTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Device\Tabs\ConfigController;
use App\Http\Controllers\Device\Tabs\ShowConfigController;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class DeviceConfigTabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        LibrenmsConfig::set('unimus.enabled', true);
        LibrenmsConfig::set('unimus.url', 'http://unimus:8085');
        LibrenmsConfig::set('unimus.api_version', 'v2');
        LibrenmsConfig::set('unimus.token', 'test-token');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testConfigTabVisibleAndLegacyHiddenWhenUnimusConfigured(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin());

        $this->assertTrue(app(ConfigController::class)->visible($device));
        $this->assertFalse(app(ShowConfigController::class)->visible($device));
    }

    public function testConfigTabHiddenWhenUnimusDisabled(): void
    {
        LibrenmsConfig::set('unimus.enabled', false);

        $device = Device::factory()->create();

        $this->actingAs($this->admin());

        $this->assertFalse(app(ConfigController::class)->visible($device));
    }

    public function testConfigTabHiddenWithoutPermission(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $this->actingAs($user);

        $this->assertFalse(app(ConfigController::class)->visible($device));
    }

    public function testUserWithoutPermissionGetsForbidden(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertForbidden();
    }

    public function testBackupsEndpointReturnsBackupList(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 2, 'validSince' => 200, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('config')],
                ],
                'paginator' => ['totalCount' => 1, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('backups.0.id', '2')
            ->assertJsonPath('backups.0.content', null);
    }

    public function testBackupsEndpointReturns404WhenDeviceNotInUnimus(): void
    {
        $device = Device::factory()->create();

        Http::fake(['*' => Http::response(['code' => 404], 404)]);

        $this->actingAs($this->admin())
            ->get(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertNotFound()
            ->assertJsonPath('error', 'device_not_found');
    }

    public function testBackupEndpointReturnsContent(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 2, 'validSince' => 200, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('interface eth0')],
                ],
                'paginator' => ['totalCount' => 1, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.config.backup', ['device' => $device->device_id, 'backup' => 2]))
            ->assertOk()
            ->assertJsonPath('content', 'interface eth0');
    }

    public function testBackupEndpointRejectsNonNumericIdForUnimus(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('device.config.backup', ['device' => $device->device_id, 'backup' => 'a1b2c3d']))
            ->assertNotFound()
            ->assertJsonPath('error', 'backup_not_found');
    }

    public function testDiffEndpointValidatesParameters(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['orig', 'rev']);

        $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id, 'orig' => 1, 'rev' => 1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rev']);
    }

    public function testDiffEndpointReturnsNormalizedGroups(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/backups/diff*' => Http::response([
                'data' => [
                    'origDeviceInfo' => ['id' => 0, 'address' => '192.168.1.1', 'type' => 'IOS'],
                    'revDeviceInfo' => ['id' => 0, 'address' => '192.168.1.1', 'type' => 'IOS'],
                    'lineGroups' => [
                        [
                            'type' => 'INSERTED',
                            'originalLines' => [],
                            'revisedLines' => [['number' => 5, 'text' => 'ntp server 10.0.0.1']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Unimus backup ids are zero-based, the very first backup has id 0
        $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id, 'orig' => 0, 'rev' => 2]))
            ->assertOk()
            ->assertJsonPath('groups.0.type', 'INSERTED')
            ->assertJsonPath('groups.0.revised.0.text', 'ntp server 10.0.0.1');
    }

    public function testDataMethodReturnsOnlyLatestBackupAndPreparedUrlsAndMessages(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups/latest' => Http::response([
                'data' => ['id' => 99, 'validSince' => 300, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('latest content')],
            ], 200),
        ]);

        $data = app(ConfigController::class)->data($device, new \Illuminate\Http\Request());

        $this->assertEquals('99', $data['latest']['id']);
        $this->assertEquals('latest content', $data['latest']['content']);
        $this->assertEmpty($data['backups']);
        $this->assertArrayHasKey('urls', $data);
        $this->assertArrayHasKey('messages', $data);
        $this->assertEquals('Unimus', $data['provider']);
    }
}
