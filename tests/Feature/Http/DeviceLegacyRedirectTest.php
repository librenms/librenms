<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DeviceLegacyRedirectTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, array{string, string, 2?: array<string, mixed>}>
     */
    public static function bookmarks(): array
    {
        return [
            'hostname' => ['{hostname}', 'device.overview'],
            'device= overview' => ['device={id}', 'device.overview'],
            'tab= overview' => ['device={id}/tab=overview', 'device.overview'],
            'tab= ports view' => ['device={id}/tab=ports/view=basic', 'device.ports', ['view' => 'basic']],
            'ports path view' => ['{id}/ports/detail', 'device.ports', ['view' => 'detail']],
            'port security' => ['{id}/tab=ports/view=portsecurity', 'device.ports.security'],
            'vlans path view' => ['{hostname}/vlans/ports', 'device.vlans', ['view' => 'ports']],
            'services view' => ['{id}/services/view=details', 'device.services', ['view' => 'details']],
            'query kept' => ['device={id}/tab=processes?order=user', 'device.processes', ['order' => 'user']],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    #[DataProvider('bookmarks')]
    public function testLegacyBookmarkRedirectsPermanently(string $bookmark, string $route, array $params = []): void
    {
        $device = Device::factory()->create();
        $path = strtr($bookmark, ['{id}' => (string) $device->device_id, '{hostname}' => $device->hostname]);

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get('/device/' . $path)
            ->assertStatus(301)
            ->assertRedirectToRoute($route, ['device' => $device] + $params);
    }

    public function testUnknownTabIsNotFound(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get("/device/$device->device_id/not-a-tab")
            ->assertNotFound();
    }

    public function testUnknownHostnameIsNotFound(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get('/device/missing-device.example.test')
            ->assertNotFound();
    }

    public function testRedirectRequiresDeviceAccess(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->get("/device/$device->hostname")
            ->assertForbidden();
    }

    public function testUnknownPathIsNotFound(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get('/some/removed/legacy=page')
            ->assertNotFound();
    }
}
