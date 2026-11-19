<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Application;
use App\Models\Component;
use App\Models\ComponentPref;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class AppsPagesTest extends TestCase
{
    use DatabaseTransactions;

    public function testDeviceAppInstanceControlsWhichGraphsRender(): void
    {
        $device = Device::factory()->create();
        $first = Application::factory()->for($device)->create(['app_type' => 'drbd', 'app_instance' => 'alpha']);
        $second = Application::factory()->for($device)->create(['app_type' => 'redis', 'app_instance' => 'beta']);
        $other = Application::factory()->create(['app_type' => 'drbd', 'app_instance' => 'gamma']);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get(route('device.apps', ['device' => $device]))
            ->assertOk()
            ->assertViewHas('selected', fn (Application $application) => $application->is($first));

        $this->get($second->deviceAppsUrl())
            ->assertOk()
            ->assertViewHas('selected', fn (Application $application) => $application->is($second))
            ->assertSee('type=application_redis_clients', false)
            ->assertSee('id=' . $second->app_id, false);

        $this->get(route('device.apps', ['device' => $device, 'app' => 'drbd', 'instance' => $other->app_id]))
            ->assertNotFound();
    }

    public function testLegacyLinksCarryAppFiltersToNamedRoutes(): void
    {
        $device = Device::factory()->create();
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get("device/device=$device->device_id/tab=apps/app=portactivity/port=443/")
            ->assertStatus(301)
            ->assertRedirect(route('device.apps', ['device' => $device, 'app' => 'portactivity', 'port' => '443']));

        $this->get("device/$device->device_id/apps/app=logsize/log_file=%2Fvar%2Flog%2Fa%2Bb/")
            ->assertStatus(301)
            ->assertRedirect(route('device.apps', ['device' => $device, 'app' => 'logsize', 'log_file' => '/var/log/a+b']));

        $this->get('apps/app=drbd/instance=a%2Bb%3Dc/')
            ->assertStatus(301)
            ->assertRedirect(route('apps.index', ['app' => 'drbd', 'instance' => 'a+b=c']));
    }

    public function testFleetPagesFilterByAccessAndInstanceName(): void
    {
        $device = Device::factory()->create();
        $visible = Application::factory()->for($device)->create(['app_type' => 'drbd', 'app_instance' => 'alpha']);
        $second = Application::factory()->create(['app_type' => 'drbd', 'app_instance' => 'beta']);
        Application::factory()->create(['app_type' => 'apache', 'app_instance' => 'hidden']);
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(Permission::findOrCreate('device.view'), Permission::findOrCreate('application.view'));
        $user->devicesOwned()->attach([$device->device_id, $second->device_id]);
        $this->actingAs($user);

        $this->get(route('apps.index'))
            ->assertOk()
            ->assertSee($visible->deviceAppsUrl())
            ->assertDontSee('application_apache_bits', false);

        $this->get(route('apps.index', ['app' => 'drbd', 'instance' => 'alpha']))
            ->assertOk()
            ->assertViewHas('applications', fn ($applications) => $applications->modelKeys() === [$visible->app_id]);

        $this->get(route('apps.index', ['app' => 'apache']))
            ->assertOk()
            ->assertSee('No applications found.');
    }

    public function testAppPagesRequireApplicationViewPermission(): void
    {
        $device = Device::factory()->create();
        Application::factory()->for($device)->create();
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(Permission::findOrCreate('device.view'));
        $user->devicesOwned()->attach($device);
        $this->actingAs($user);

        $this->get(route('apps.index'))->assertForbidden();
        $this->get(route('device.apps', ['device' => $device]))->assertForbidden();
    }

    public function testAppFilterValuesKeepTheirOriginalCharactersInGraphUrls(): void
    {
        $source = 'clock/1 & 2';
        $application = Application::factory()->create([
            'app_type' => 'chronyd',
            'app_instance' => 'main',
            'data' => ['sources' => [$source]],
        ]);

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get($application->deviceAppsUrl(['source' => $source]))
            ->assertOk()
            ->assertSee('type=application_chronyd_source_sampling', false)
            ->assertSee('source=clock%2F1%20%26%202', false);
    }

    public function testLegacyNfsDataStillRendersTheMountTable(): void
    {
        $application = Application::factory()->create([
            'app_type' => 'nfs',
            'data' => [
                'is_client' => true,
                'mounts' => [['host' => '192.0.2.1', 'rpath' => '/data', 'lpath' => '/mnt', 'opts' => ['vers' => '4']]],
            ],
        ]);

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get($application->deviceAppsUrl(['app_page' => 'mounts']))
            ->assertOk()
            ->assertSee('/data')
            ->assertSee('/mnt')
            ->assertSee('vers=4');
    }

    public function testNtpKeepsItsFleetAndDevicePeerViews(): void
    {
        $application = Application::factory()->create(['app_type' => 'ntp']);
        $component = Component::factory()->create(['device_id' => $application->device_id, 'type' => 'ntp']);
        ComponentPref::create(['component' => $component->id, 'attribute' => 'peer', 'value' => '192.0.2.1']);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get($application->deviceAppsUrl())
            ->assertOk()
            ->assertSee('192.0.2.1')
            ->assertSee('type=device_ntp_stratum', false);

        $this->get(route('apps.index', ['app' => 'ntp', 'view' => 'error', 'graph' => 'delay']))
            ->assertOk()
            ->assertSee('ntp-table', false)
            ->assertViewHasAll(['peerView' => 'error', 'peerGraph' => 'delay']);
    }
}
