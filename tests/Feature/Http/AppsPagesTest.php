<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\Rrd;
use App\Models\Application;
use App\Models\Component;
use App\Models\ComponentPref;
use App\Models\Device;
use App\Models\Port;
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

    public function testOptionBarsShowAppStateAndSelectedFilter(): void
    {
        $application = Application::factory()->create([
            'app_type' => 'chronyd',
            'app_state' => 'ERROR',
            'data' => ['sources' => ['clock-a', 'clock-b']],
        ]);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $content = $this->get($application->deviceAppsUrl(['source' => 'clock-b']))
            ->assertOk()
            ->assertSee('title="Error"', false)
            ->assertSee('fa-close text-danger', false)
            ->getContent();

        $this->assertMatchesRegularExpression('#class="pagemenu-selected">\s*<a [^>]*>clock-b</a>#', $content);
        $this->assertDoesNotMatchRegularExpression('#class="pagemenu-selected">\s*<a [^>]*>(Tracking|clock-a)</a>#', $content);

        $this->get(route('apps.index'))
            ->assertOk()
            ->assertSee('fa-close text-danger', false);
    }

    public function testSaganGraphsUseTheSelectedInstance(): void
    {
        $application = Application::factory()->create(['app_type' => 'sagan', 'data' => ['instances' => ['ids1']]]);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $content = $this->get($application->deviceAppsUrl(['sinstance' => 'ids1']))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('#/graph\?[^"]*sinstance=ids1#', $content);
    }

    public function testHttpAccessLogDetailsAndSetsAreNotSelectedTogether(): void
    {
        $application = Application::factory()->create(['app_type' => 'http_access_log_combined', 'data' => ['logs' => ['site1']]]);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $content = $this->get($application->deviceAppsUrl(['access_log_page' => 'bytes', 'log' => 'site1']))
            ->assertOk()
            ->assertSee('log_stat=bytes_max', false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('#/graph\?[^"]*log=site1#', $content);

        $this->assertMatchesRegularExpression('#class="pagemenu-selected">\s*<a [^>]*>Bytes</a>#', $content);
        $this->assertDoesNotMatchRegularExpression('#class="pagemenu-selected">\s*<a [^>]*>site1</a>#', $content);
    }

    public function testDhcpLeasesLinkKnownPorts(): void
    {
        $port = Port::factory()->for(Device::factory())->create(['ifPhysAddress' => '001122334455']);
        $lease = ['ip' => '192.0.2.10', 'state' => 'active', 'starts' => 0, 'ends' => 0];
        $application = Application::factory()->create(['app_type' => 'dhcp-stats', 'data' => [
            'pools' => [],
            'found_leases' => [$lease + ['hw_address' => '00:11:22:33:44:55'], $lease + ['hw_address' => 'aa:bb:cc:dd:ee:ff']],
        ]]);
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get($application->deviceAppsUrl(['app_page' => 'leases']))
            ->assertOk()
            ->assertSee(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id]), false)
            ->assertSee('aa:bb:cc:dd:ee:ff');
    }

    public function testRrdNamedGraphsUseTheFileNameOnly(): void
    {
        $device = Device::factory()->create(['hostname' => 'host-pool-a']);
        $ceph = Application::factory()->for($device)->create(['app_type' => 'ceph']);
        $shoutcast = Application::factory()->for($device)->create(['app_type' => 'shoutcast']);
        $other = $shoutcast->app_id . '0';
        Rrd::partialMock()->shouldReceive('getRrdFiles')->andReturnUsing(fn (string $hostname, array $prefix) => match ($prefix[1] . '-' . ($prefix[3] ?? '')) {
            'ceph-pool' => ["host-pool-a/app-ceph-$ceph->app_id-pool-rbd.rrd"],
            'shoutcast-' => [
                "host-pool-a/app-shoutcast-$shoutcast->app_id-radio-one.example_8000.rrd",
                "host-pool-a/app-shoutcast-$other-other_9000.rrd",
            ],
            default => [],
        });
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get($ceph->deviceAppsUrl())
            ->assertOk()
            ->assertSee('rbd Reads/Writes')
            ->assertDontSee('a/app-ceph');

        $this->get($shoutcast->deviceAppsUrl())
            ->assertOk()
            ->assertSee('radio-one.example (Port: 8000)')
            ->assertDontSee('other (Port: 9000)');
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
        $this->ntpPeer($application->device_id, '192.0.2.1');
        $this->ntpPeer($application->device_id, '192.0.2.9', 'unreachable');
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));

        $this->get($application->deviceAppsUrl())
            ->assertOk()
            ->assertSee('192.0.2.1')
            ->assertSee('type=device_ntp_stratum', false);

        $this->get(route('apps.index', ['app' => 'ntp', 'view' => 'error', 'graph' => 'delay']))
            ->assertOk()
            ->assertSee('192.0.2.9')
            ->assertSee('unreachable')
            ->assertDontSee('192.0.2.1')
            ->assertSee('type=device_ntp_delay', false)
            ->assertSee('href="' . route('device.apps', ['device' => $application->device_id, 'app' => 'ntp']) . '"', false)
            ->assertDontSee('&lt;table', false);
    }

    public function testNtpFleetOnlyListsAccessibleDevices(): void
    {
        $accessible = Device::factory()->create();
        $this->ntpPeer($accessible->device_id, '192.0.2.1');
        $this->ntpPeer(Device::factory()->create()->device_id, '192.0.2.2');
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(Permission::findOrCreate('device.view'), Permission::findOrCreate('application.view'));
        $user->devicesOwned()->attach($accessible->device_id);

        $this->actingAs($user)
            ->get(route('apps.index', ['app' => 'ntp']))
            ->assertOk()
            ->assertSee('192.0.2.1')
            ->assertDontSee('192.0.2.2')
            ->assertSee('href="' . route('device', $accessible->device_id) . '"', false)
            ->assertDontSee(route('device.apps', ['device' => $accessible->device_id]));
    }

    private function ntpPeer(int $deviceId, string $peer, ?string $error = null): void
    {
        $component = Component::factory()->create(['device_id' => $deviceId, 'type' => 'ntp', 'label' => $peer, 'status' => $error === null ? 0 : 2, 'error' => $error ?? '']);
        foreach (['peer' => $peer, 'stratum' => '2'] as $attribute => $value) {
            ComponentPref::create(['component' => $component->id, 'attribute' => $attribute, 'value' => $value]);
        }
    }
}
