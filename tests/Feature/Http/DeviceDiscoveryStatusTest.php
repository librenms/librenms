<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class DeviceDiscoveryStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testUndiscoveredDeviceReportsNotDiscovered(): void
    {
        $device = Device::factory()->create(['last_discovered' => null]);

        $this->actingAs($this->admin())
            ->getJson(route('device.discovery-status', $device))
            ->assertOk()
            ->assertExactJson(['discovered' => false]);
    }

    public function testDiscoveredDeviceReportsDiscovered(): void
    {
        $device = Device::factory()->create(['last_discovered' => now()]);

        $this->actingAs($this->admin())
            ->getJson(route('device.discovery-status', $device))
            ->assertOk()
            ->assertExactJson(['discovered' => true]);
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->getJson(route('device.discovery-status', $device))
            ->assertForbidden();
    }

    public function testDevicePageShowsAwaitingDiscoveryNotice(): void
    {
        $device = Device::factory()->create(['last_discovered' => null]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'notes']))
            ->assertOk()
            ->assertSee(__('device.awaiting_discovery'))
            ->assertSee(route('device.discovery-status', $device->device_id), false)
            ->assertSee('window.location.reload()', false)
            ->assertDontSee(__('device.discovery_complete'));
    }

    public function testDeviceEditPageShowsClickToRefreshNotice(): void
    {
        $device = Device::factory()->create(['last_discovered' => null]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.misc', $device))
            ->assertOk()
            ->assertSee(__('device.awaiting_discovery_edit'))
            ->assertSee(__('device.discovery_complete'))
            ->assertDontSee(__('device.awaiting_discovery'))
            ->assertSee(route('device.discovery-status', $device->device_id), false);
    }

    public function testDevicePageHidesNoticeOnceDiscovered(): void
    {
        $device = Device::factory()->create(['last_discovered' => now()]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'notes']))
            ->assertOk()
            ->assertDontSee(__('device.awaiting_discovery'));
    }

    public function testDisabledDevicePageHidesNotice(): void
    {
        $device = Device::factory()->create(['last_discovered' => null, 'disabled' => 1]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'notes']))
            ->assertOk()
            ->assertDontSee(__('device.awaiting_discovery'));
    }
}
