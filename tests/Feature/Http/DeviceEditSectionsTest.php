<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Application;
use App\Models\Component;
use App\Models\Customoid;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DeviceEditSectionsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testApplicationsListsPollersAndToggles(): void
    {
        $device = Device::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('device.edit.apps', $device))
            ->assertOk()
            ->assertSee('Apache');

        $this->actingAs($admin)
            ->putJson(route('device.edit.apps.update', [$device, 'apache']), ['enabled' => true])
            ->assertOk();
        $this->assertTrue($device->applications()->where('app_type', 'apache')->exists());

        $this->actingAs($admin)
            ->putJson(route('device.edit.apps.update', [$device, 'apache']), ['enabled' => false])
            ->assertOk();
        $this->assertFalse($device->applications()->where('app_type', 'apache')->exists());
        $this->assertTrue(Application::withTrashed()->where('device_id', $device->device_id)->where('app_type', 'apache')->exists());

        $this->actingAs($admin)
            ->putJson(route('device.edit.apps.update', [$device, 'not-an-app']), ['enabled' => true])
            ->assertNotFound();
    }

    public function testApplicationsRequirePermission(): void
    {
        $device = Device::factory()->create();
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)
            ->putJson(route('device.edit.apps.update', [$device, 'apache']), ['enabled' => true])
            ->assertForbidden();
    }

    public function testComponentsSaveChangedSettings(): void
    {
        $device = Device::factory()->create();
        $component = Component::factory()->create(['device_id' => $device->device_id, 'type' => 'test', 'label' => 'Comp <1>', 'disabled' => 0, 'ignore' => 0]);
        $other = Component::factory()->create(['type' => 'test', 'disabled' => 0, 'ignore' => 0]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('device.edit.components', $device))
            ->assertOk()
            ->assertSee('Comp ')
            ->assertDontSee('Comp <1>', false);

        $this->actingAs($admin)
            ->putJson(route('device.edit.components.update', $device), ['components' => [
                $component->id => ['disabled' => true, 'ignore' => false],
                $other->id => ['disabled' => true, 'ignore' => true],
            ]])
            ->assertOk()
            ->assertJson(['message' => '1 component updated']);

        $this->assertEquals(1, $component->fresh()->disabled);
        $this->assertEquals(0, $other->fresh()->disabled);
        $this->assertTrue(Eventlog::where('device_id', $device->device_id)->where('type', 'component')->exists());
    }

    public function testComponentsUpdateIsScopedToAccessibleDevices(): void
    {
        $accessible = Device::factory()->create();
        $other = Device::factory()->create();
        $mine = Component::factory()->create(['device_id' => $accessible->device_id, 'type' => 'test', 'disabled' => 0, 'ignore' => 0]);
        $theirs = Component::factory()->create(['device_id' => $other->device_id, 'type' => 'test', 'disabled' => 0, 'ignore' => 0]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole(Role::findOrCreate('user'));
        $user->givePermissionTo(Permission::findOrCreate('component.update'));
        $user->devicesOwned()->attach($accessible->device_id);

        $this->actingAs($user)
            ->putJson(route('device.edit.components.update', $other), ['components' => [$theirs->id => ['disabled' => true, 'ignore' => true]]])
            ->assertForbidden();
        $this->assertEquals(0, $theirs->fresh()->disabled);

        // components of other devices are ignored even through an accessible device
        $this->actingAs($user)
            ->putJson(route('device.edit.components.update', $accessible), ['components' => [
                $mine->id => ['disabled' => true, 'ignore' => false],
                $theirs->id => ['disabled' => true, 'ignore' => true],
            ]])
            ->assertOk();
        $this->assertEquals(1, $mine->fresh()->disabled);
        $this->assertEquals(0, $theirs->fresh()->disabled);
    }

    public function testCustomoidsList(): void
    {
        $device = Device::factory()->create();
        Customoid::create(['device_id' => $device->device_id, 'customoid_descr' => 'Temp <A>', 'customoid_oid' => '.1.3.6.1', 'customoid_unit' => 'C']);

        $this->actingAs($this->admin())
            ->get(route('device.edit.customoids', $device))
            ->assertOk()
            ->assertSee('Temp &lt;A&gt;', false)
            ->assertSee('.1.3.6.1')
            ->assertSee('Add New OID');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
