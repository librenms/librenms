<?php

/**
 * ApiRenameDeviceTest.php
 *
 * Verifies that the API only renames devices to a valid hostname or IP.
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

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ApiRenameDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @return array{X-Auth-Token: string}
     */
    private function adminHeaders(): array
    {
        /** @var User $user */
        $user = User::factory()->admin()->create(['enabled' => 1]);

        return ['X-Auth-Token' => $user->createToken('test')->plainTextToken];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidHostnames(): array
    {
        return [
            'parent directory' => ['..'],
            'backslash traversal' => ['..\\..\\etc\\shadow'],
            'leading dot' => ['.hidden'],
            'space' => ['foo bar'],
            'shell characters' => ['foo;id'],
            'quote' => ["foo'bar"],
        ];
    }

    #[DataProvider('invalidHostnames')]
    public function testRenameRejectsInvalidHostname(string $newHostname): void
    {
        $device = Device::factory()->create();
        $hostname = $device->hostname;

        $this->json('PATCH', "/api/v0/devices/$hostname/rename/" . rawurlencode($newHostname), [], $this->adminHeaders())
            ->assertStatus(400)
            ->assertJsonPath('status', 'error');

        $this->assertSame($hostname, $device->refresh()->hostname);
    }

    public function testRenameAcceptsHostnameAndIp(): void
    {
        $device = Device::factory()->create();
        $headers = $this->adminHeaders();

        $this->json('PATCH', "/api/v0/devices/$device->hostname/rename/renamed-host.example.com", [], $headers)->assertStatus(200);
        $this->assertSame('renamed-host.example.com', $device->refresh()->hostname);

        $this->json('PATCH', "/api/v0/devices/$device->device_id/rename/2001:db8::1", [], $headers)->assertStatus(200);
        $this->assertSame('2001:db8::1', $device->refresh()->hostname);
    }

    public function testRenameWithSlashNeverReachesTheDevice(): void
    {
        $device = Device::factory()->create();
        $hostname = $device->hostname;
        $headers = $this->adminHeaders();

        // a (decoded) slash does not match the route, so these fall through to the api 404
        foreach (['../../../../etc/shadow', 'foo/bar', '192.0.2.0/24'] as $newHostname) {
            $this->json('PATCH', "/api/v0/devices/$hostname/rename/" . rawurlencode($newHostname), [], $headers)->assertStatus(404);
        }

        $this->assertSame($hostname, $device->refresh()->hostname);
    }

    public function testUpdateRejectsHostnameField(): void
    {
        $device = Device::factory()->create();
        $hostname = $device->hostname;
        $headers = $this->adminHeaders();

        $this->json('PATCH', "/api/v0/devices/$hostname", ['field' => 'hostname', 'data' => '../../etc/shadow'], $headers)
            ->assertStatus(500);
        $this->json('PATCH', "/api/v0/devices/$hostname", ['field' => ['hostname'], 'data' => ['../../etc/shadow']], $headers)
            ->assertStatus(500);

        $this->assertSame($hostname, $device->refresh()->hostname);
    }
}
