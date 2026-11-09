<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PackagesPageTest extends TestCase
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

    public function testListsPackagesGroupedByNameAndVersion(): void
    {
        $first = Device::factory()->create(['hostname' => 'pkg-host-one.example.com']);
        $second = Device::factory()->create(['hostname' => 'pkg-host-two.example.com']);
        Package::factory()->for($first)->create(['name' => 'pkgtest-openssl', 'version' => '3.0.2', 'build' => '1', 'arch' => 'amd64', 'manager' => 'deb']);
        Package::factory()->for($second)->create(['name' => 'pkgtest-openssl', 'version' => '3.0.13', 'build' => '', 'arch' => 'amd64', 'manager' => 'deb']);
        Package::factory()->for($first)->create(['name' => 'pkgtest-nginx', 'version' => '1.24.0', 'arch' => 'amd64', 'manager' => 'deb']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-openssl']]]))
            ->assertOk()
            ->assertSee('pkgtest-openssl')
            ->assertSee('3.0.2-1')
            ->assertSee('3.0.13')
            ->assertSee('pkg-host-two')
            ->assertDontSee('pkgtest-nginx')
            ->assertViewHas('packages', fn ($packages) => $packages->get('pkgtest-openssl')['device_count'] == 2)
            ->assertViewHas('names', fn ($names) => $names->total() == 1);
    }

    public function testVersionComparison(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-cmp', 'version' => '1:2.10.1', 'build' => '', 'arch' => 'all']);
        Package::factory()->for(Device::factory())->create(['name' => 'pkgtest-cmp', 'version' => '1:2.9.0', 'build' => '', 'arch' => 'all']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-cmp'], 'version' => ['lt' => '1:2.10']]]))
            ->assertOk()
            ->assertSee('1:2.9.0')
            ->assertDontSee('1:2.10.1');

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-cmp'], 'version' => ['contains' => '2.10']]]))
            ->assertOk()
            ->assertSee('1:2.10.1')
            ->assertDontSee('1:2.9.0');
    }

    public function testVersionComparisonUsesEpochFirst(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-epoch-one', 'version' => '1:1.0.0']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-epoch-none', 'version' => '3.0.0']);
        $filter = fn (string $op, string $version) => ['filter' => ['name' => ['starts_with' => 'pkgtest-epoch-'], 'version' => [$op => $version]]];

        // a missing epoch is 0, so 1:1.0.0 is newer than 3.0.0
        $this->actingAs($this->admin())
            ->get(route('packages.index', $filter('gt', '3.0.0')))
            ->assertOk()
            ->assertSee('pkgtest-epoch-one')
            ->assertDontSee('pkgtest-epoch-none');

        $this->actingAs($this->admin())
            ->get(route('packages.index', $filter('lt', '1:0.5')))
            ->assertOk()
            ->assertSee('pkgtest-epoch-none')
            ->assertDontSee('pkgtest-epoch-one');

        // same epoch compares the rest of the version
        $this->actingAs($this->admin())
            ->get(route('packages.index', $filter('gte', '1:1.0.0')))
            ->assertOk()
            ->assertSee('pkgtest-epoch-one')
            ->assertDontSee('pkgtest-epoch-none');
    }

    public function testVersionComparisonFiltersBeforePagination(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->count(51)
            ->sequence(fn ($sequence) => ['name' => "pkgtest-page-a$sequence->index", 'version' => '1.0.0'])
            ->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-page-z', 'version' => '2.0.0']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['starts_with' => 'pkgtest-page-'], 'version' => ['gte' => '2.0']]]))
            ->assertOk()
            ->assertSee('pkgtest-page-z')
            ->assertDontSee('pkgtest-page-a')
            ->assertViewHas('names', fn ($names) => $names->total() == 1);
    }

    public function testExactNameMatch(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-php']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-php-cli']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['contains' => 'pkgtest-php']]]))
            ->assertOk()
            ->assertSee('pkgtest-php-cli');

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-php']]]))
            ->assertOk()
            ->assertSee('pkgtest-php')
            ->assertDontSee('pkgtest-php-cli');
    }

    public function testOnlyAccessibleDevices(): void
    {
        $visible = Device::factory()->create();
        Package::factory()->for($visible)->create(['name' => 'pkgtest-visible']);
        Package::factory()->for(Device::factory())->create(['name' => 'pkgtest-hidden']);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($visible->device_id);

        $this->actingAs($user)
            ->get(route('packages.index', ['filter' => ['name' => ['contains' => 'pkgtest-']]]))
            ->assertOk()
            ->assertSee('pkgtest-visible')
            ->assertDontSee('pkgtest-hidden');
    }

    public function testDeviceTabFiltersOnlyThatDevice(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-tab-new', 'version' => '2.10.0']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-tab-old', 'version' => '2.9.0']);
        Package::factory()->for(Device::factory())->create(['name' => 'pkgtest-tab-other', 'version' => '3.0.0']);

        $this->actingAs($this->admin())
            ->get(url("device/$device->device_id/packages"))
            ->assertOk()
            ->assertSee('pkgtest-tab-new')
            ->assertSee('pkgtest-tab-old')
            ->assertDontSee('pkgtest-tab-other')
            ->assertSee('device.packages');

        $this->actingAs($this->admin())
            ->get(url("device/$device->device_id/packages") . '?' . http_build_query(['filter' => ['version' => ['gte' => '2.10']]]))
            ->assertOk()
            ->assertSee('pkgtest-tab-new')
            ->assertDontSee('pkgtest-tab-old')
            ->assertDontSee('pkgtest-tab-other');
    }

    public function testDeviceTabSizeFilterAndIgnoresDeviceId(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-tab-big', 'size' => 5000]);
        Package::factory()->for($device)->create(['name' => 'pkgtest-tab-small', 'size' => 100]);
        Package::factory()->for($other)->create(['name' => 'pkgtest-tab-other', 'size' => 5000]);
        $tab = url("device/$device->device_id/packages") . '?';

        $this->actingAs($this->admin())
            ->get($tab . http_build_query(['filter' => ['size' => ['gte' => 1000]]]))
            ->assertOk()
            ->assertSee('pkgtest-tab-big')
            ->assertDontSee('pkgtest-tab-small');

        $this->actingAs($this->admin())
            ->get($tab . http_build_query(['filter' => ['device_id' => ['eq' => $other->device_id]]]))
            ->assertOk()
            ->assertSee('pkgtest-tab-big')
            ->assertSee('pkgtest-tab-small')
            ->assertDontSee('pkgtest-tab-other');
    }

    public function testManagerFilter(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-mgr-deb', 'manager' => 'deb']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-mgr-rpm', 'manager' => 'rpm']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['filter' => ['name' => ['starts_with' => 'pkgtest-mgr-'], 'manager' => ['eq' => 'deb']]]))
            ->assertOk()
            ->assertSee('pkgtest-mgr-deb')
            ->assertDontSee('pkgtest-mgr-rpm');
    }

    public function testSelectOptionsOnlyAvailableValues(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-opt-a', 'manager' => 'mgr-b', 'arch' => 'arch-b']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-opt-b', 'manager' => 'mgr-a', 'arch' => 'arch-a']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-opt-c', 'manager' => 'mgr-a', 'arch' => 'arch-a']);
        Package::factory()->for(Device::factory())->create(['name' => 'pkgtest-opt-d', 'manager' => 'mgr-c', 'arch' => 'arch-c']);

        $admin = $this->admin();
        $options = fn (string $key, User $user, ?int $deviceId = null) => collect(Package::filterFieldDefinitions($user, $deviceId))->firstWhere('key', $key)['options'];

        $this->assertSame(['mgr-a', 'mgr-b'], $options('manager', $admin, $device->device_id));
        $this->assertSame(['arch-a', 'arch-b'], $options('arch', $admin, $device->device_id));
        $this->assertSame(['mgr-a', 'mgr-b', 'mgr-c'], array_values(array_intersect($options('manager', $admin), ['mgr-a', 'mgr-b', 'mgr-c'])));
        $this->assertSame(['arch-a', 'arch-b', 'arch-c'], array_values(array_intersect($options('arch', $admin), ['arch-a', 'arch-b', 'arch-c'])));

        // only values from devices the user can access
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($device->device_id);

        $this->assertSame(['mgr-a', 'mgr-b'], $options('manager', $user));
        $this->assertSame(['arch-a', 'arch-b'], $options('arch', $user));
    }

    public function testFilterFieldsPerPage(): void
    {
        $admin = $this->admin();
        $keys = fn (?int $deviceId) => array_column(Package::filterFieldDefinitions($admin, $deviceId), 'key');

        $this->assertSame(['name', 'version', 'arch', 'manager', 'device_id'], $keys(null));
        $this->assertSame(['name', 'version', 'arch', 'manager', 'size'], $keys(1));
    }

    public function testEmptyMessages(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-empty']);
        $filter = ['filter' => ['name' => ['eq' => 'pkgtest-no-such-package']]];

        $this->actingAs($this->admin())
            ->get(route('packages.index', $filter))
            ->assertOk()
            ->assertSee('No packages found');

        $this->actingAs($this->admin())
            ->get(url("device/$device->device_id/packages") . '?' . http_build_query($filter))
            ->assertOk()
            ->assertSee('No packages found for this device.');
    }

    public function testUrlFiltersReplaceSavedFilter(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-saved-deb', 'manager' => 'deb']);
        Package::factory()->for($device)->create(['name' => 'pkgtest-saved-rpm', 'manager' => 'rpm']);
        $admin = $this->admin();
        UserPref::setPref($admin, 'filters.packages', ['manager' => ['eq' => 'deb'], 'name' => ['starts_with' => 'pkgtest-saved-']]);

        $this->actingAs($admin)
            ->get(route('packages.index'))
            ->assertOk()
            ->assertSee('pkgtest-saved-deb')
            ->assertDontSee('pkgtest-saved-rpm');

        $this->actingAs($admin)
            ->get(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-saved-rpm']]]))
            ->assertOk()
            ->assertSee('pkgtest-saved-rpm')
            ->assertViewHas('filter', ['name' => ['eq' => 'pkgtest-saved-rpm']]);

        $this->actingAs($admin)
            ->get(route('packages.index') . '?filter=')
            ->assertOk()
            ->assertViewHas('filter', []);
    }

    public function testLegacyUrlRedirects(): void
    {
        $this->actingAs($this->admin())
            ->get('packages/name=pkgtest-legacy/')
            ->assertRedirect(route('packages.index', ['filter' => ['name' => ['eq' => 'pkgtest-legacy']]]));
    }
}
