<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DeviceEditRedirectTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, array{string, string, 2?: array<string, mixed>}>
     */
    public static function bookmarks(): array
    {
        return [
            'default' => ['device={id}/tab=edit', 'device.edit'],
            'default alias' => ['{id}/edit/section=default', 'device.edit'],
            'device settings' => ['device={hostname}/tab=edit/section=device', 'device.edit'],
            'edit alias' => ['{id}/edit/section=edit', 'device.edit'],
            'alert rules' => ['{id}/edit/section=alert-rules/per_page=10?page=2', 'device.edit.alert-rules', ['page' => 2, 'per_page' => 10]],
            'applications settings' => ['{id}/edit/section=apps', 'device.edit.apps'],
            'components' => ['{id}/edit/section=component', 'device.edit.components'],
            'custom OIDs' => ['{id}/edit/section=customoid', 'device.edit.customoids'],
            'health' => ['{id}/edit/section=health', 'device.edit.health'],
            'memory' => ['{id}/edit/section=mempools', 'device.edit.mempools'],
            'misc via query' => ['device={id}/tab=edit?section=misc', 'device.edit.misc'],
            'modules' => ['{id}/edit/section=modules', 'device.edit.modules'],
            'polling tab' => ['{id}/edit/section=polling?tab=icmp', 'device.edit.polling', ['tab' => 'icmp']],
            'SNMP' => ['{id}/edit/section=snmp', 'device.edit.polling', ['tab' => 'snmp']],
            'IPMI' => ['{id}/edit/section=ipmi', 'device.edit.polling', ['tab' => 'ipmi']],
            'ports filter' => ['{id}/edit/section=ports?filter[ifName][eq]=eth0', 'device.edit.ports', ['filter' => ['ifName' => ['eq' => 'eth0']]]],
            'clear ports filter' => ['{id}/edit/section=ports?filter=', 'device.edit.ports', ['filter' => '']],
            'processors' => ['{id}/edit/section=processors', 'device.edit.processors'],
            'routing' => ['{id}/edit/section=routing', 'device.edit.routing'],
            'services' => ['device={id}/tab=edit/section=services?section=health', 'device.edit.services'],
            'storage' => ['{id}/edit/section=storage', 'device.edit.storage'],
            'wireless sensors' => ['{id}/edit/section=wireless-sensors', 'device.edit.wireless-sensors'],
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

    public function testCanonicalSettingsRouteRendersDirectly(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get(route('device.edit', $device))
            ->assertOk()
            ->assertViewIs('device.edit.device');
    }

    public function testUnknownSectionReturnsNotFound(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get("/device/{$device->device_id}/edit/section=ports.list")
            ->assertNotFound();
    }

    public function testLegacyBookmarkForMissingDeviceReturnsNotFound(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get('/device/device=missing-device.example.test/tab=edit/section=health')
            ->assertNotFound();
    }

    public function testLegacyBookmarkRequiresDeviceViewPermission(): void
    {
        $device = Device::factory()->create();
        $user = User::factory()->create(['enabled' => 1])->assignRole('user');

        $this->actingAs($user)
            ->get("/device/{$device->device_id}/edit/section=health")
            ->assertForbidden();
    }

    public function testNativeSettingsRouteRequiresEditPermission(): void
    {
        $device = Device::factory()->create();
        $user = User::factory()->read()->create(['enabled' => 1]);

        $this->actingAs($user)
            ->get("/device/device={$device->hostname}/tab=edit")
            ->assertStatus(301)
            ->assertRedirectToRoute('device.edit', $device);

        $this->get(route('device.edit', $device))->assertForbidden();
    }
}
