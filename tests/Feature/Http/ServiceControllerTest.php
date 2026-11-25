<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class ServiceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private string $pluginDir;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        LibrenmsConfig::set('show_services', true);

        $this->pluginDir = sys_get_temp_dir() . '/librenms-plugins-' . uniqid();
        mkdir($this->pluginDir);
        touch($this->pluginDir . '/check_http');
        LibrenmsConfig::set('nagios_plugins', $this->pluginDir);
    }

    protected function tearDown(): void
    {
        unlink($this->pluginDir . '/check_http');
        rmdir($this->pluginDir);

        parent::tearDown();
    }

    public function testTemplateShowRedirectsToTemplatesList(): void
    {
        $template = ServiceTemplate::factory()->create();
        $admin = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($admin)
            ->get(route('services.templates.show', ['template' => $template]))
            ->assertRedirectToRoute('services.templates.index');
    }

    public function testIndexGroupsServicesByDeviceAndFiltersByState(): void
    {
        $device = Device::factory()->create(['hostname' => 'services-host.example.com']);
        Service::factory()->for($device)->create(['service_name' => 'Healthy', 'service_status' => 0, 'service_ignore' => 0, 'service_disabled' => 0]);
        Service::factory()->for($device)->create(['service_name' => 'Broken', 'service_status' => 2, 'service_ignore' => 0, 'service_disabled' => 0]);
        Service::factory()->for($device)->create(['service_name' => 'Ignored check', 'service_status' => 2, 'service_ignore' => 1, 'service_disabled' => 0]);
        Service::factory()->for($device)->create(['service_name' => 'Disabled check', 'service_status' => 2, 'service_ignore' => 0, 'service_disabled' => 1]);

        $this->actingAs($this->admin())
            ->get(route('services.index'))
            ->assertOk()
            ->assertSee('services-host.example.com')
            ->assertSee('Healthy')
            ->assertSee('Broken')
            ->assertSee('x-bind:disabled="busy || false"', false)
            ->assertSee("x-on:change=\"toggleService(\$el, '", false);

        $this->actingAs($this->admin())
            ->get(route('services.index', ['state' => 'critical']))
            ->assertOk()
            ->assertSee('Broken')
            ->assertDontSee('Healthy')
            ->assertDontSee('Ignored check')
            ->assertDontSee('Disabled check');

        $this->get(route('services.index', ['state' => 'ignored']))
            ->assertOk()
            ->assertSee('Ignored check')
            ->assertDontSee('Disabled check');

        $this->get(route('services.index', ['state' => 'disabled']))
            ->assertOk()
            ->assertSee('Disabled check')
            ->assertDontSee('Ignored check');
    }

    public function testIndexOnlyShowsServicesOfAccessibleDevices(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create(['service_name' => 'Hidden Service']);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('service.view'));

        $this->actingAs($user)
            ->get(route('services.index'))
            ->assertOk()
            ->assertDontSee('Hidden Service');
    }

    public function testIndexPaginatesWholeDeviceGroupsWithTheSelectedFilter(): void
    {
        Device::factory()->count(25)->has(Service::factory()->state([
            'service_disabled' => 1,
        ]))->sequence(fn ($sequence) => ['hostname' => sprintf('a-service-%02d.example.com', $sequence->index)])->create();
        $device = Device::factory()->create(['hostname' => 'z-service.example.com']);
        Service::factory()->for($device)->create(['service_disabled' => 1, 'service_name' => 'First check on last device']);
        Service::factory()->for($device)->create(['service_disabled' => 1, 'service_name' => 'Second check on last device']);

        $this->actingAs($this->admin());
        $filters = ['state' => 'disabled'];

        $this->get(route('services.index', $filters))
            ->assertOk()
            ->assertDontSee('z-service.example.com')
            ->assertSee(route('services.index', $filters + ['page' => 2]));

        $this->get(route('services.index', $filters + ['page' => 2]))
            ->assertOk()
            ->assertSee('First check on last device')
            ->assertSee('Second check on last device');
    }

    public function testStoreCreatesService(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('services.store'), [
                'device_id' => $device->device_id,
                'service_type' => 'http',
                'service_name' => '<b>Web</b>',
                'service_ip' => '192.0.2.1',
                'service_ignore' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Service Web added');

        $service = Service::where('device_id', $device->device_id)->sole();
        $this->assertSame('Web', $service->service_name);
        $this->assertSame('192.0.2.1', $service->service_ip);
        $this->assertSame(1, (int) $service->service_ignore);
    }

    public function testStoreRejectsUnknownCheckType(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('services.store'), ['device_id' => $device->device_id, 'service_type' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_type');
    }

    public function testShowUpdateAndDestroy(): void
    {
        $service = Service::factory()->for(Device::factory())->create(['service_name' => 'Before', 'service_disabled' => 0]);

        $this->actingAs($this->admin())
            ->getJson(route('services.show', $service))
            ->assertOk()
            ->assertJsonPath('service_name', 'Before');

        $this->getJson(route('services.edit', $service))
            ->assertOk()
            ->assertJsonPath('service_disabled', false);

        $this->actingAs($this->admin())
            ->patchJson(route('services.update', $service), ['service_name' => 'After', 'service_disabled' => true])
            ->assertOk()
            ->assertSessionHas('toasts.0.message', 'Service After updated');

        $service->refresh();
        $this->assertSame('After', $service->service_name);
        $this->assertSame(1, (int) $service->service_disabled);

        $this->actingAs($this->admin())
            ->deleteJson(route('services.destroy', $service))
            ->assertOk();

        $this->assertModelMissing($service);
    }

    public function testUserWithoutPermissionCannotModifyServices(): void
    {
        $device = Device::factory()->create();
        $service = Service::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($device);

        $this->actingAs($user)->get(route('services.index'))
            ->assertOk()
            ->assertSee('x-bind:disabled="busy || true"', false);

        $this->actingAs($user)->postJson(route('services.store'), ['device_id' => $device->device_id, 'service_type' => 'http'])->assertForbidden();
        $this->actingAs($user)->getJson(route('services.edit', $service))->assertForbidden();
        $this->actingAs($user)->patchJson(route('services.update', $service), ['service_name' => 'x'])->assertForbidden();
        $this->actingAs($user)->deleteJson(route('services.destroy', $service))->assertForbidden();
    }

    public function testDeviceEditServicesPage(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create(['service_name' => 'Edit Page Service']);

        $this->actingAs($this->admin())
            ->get(route('device.edit.services', $device))
            ->assertOk()
            ->assertSee('Edit Page Service')
            ->assertSee('createService()');

        $this->get('/device/device=' . $device->device_id . '/tab=edit/section=services')
            ->assertRedirect(route('device.edit.services', $device));
    }

    public function testLegacyDashboardServiceLinksRedirectToFilters(): void
    {
        $this->actingAs($this->admin());

        $this->get('/services/state=ok/view=details')
            ->assertRedirect(route('services.index', ['state' => 'ok']));
        $this->get('/services/view=details/ignore=1')
            ->assertRedirect(route('services.index', ['state' => 'ignored']));
        $this->get('/services/view=details/disabled=1')
            ->assertRedirect(route('services.index', ['state' => 'disabled']));
    }

    public function testAddsrvRedirectsToAddDialog(): void
    {
        $this->actingAs($this->admin())
            ->get('/addsrv')
            ->assertRedirect(route('services.index') . '#create-service');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
