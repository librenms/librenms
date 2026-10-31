<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Package;
use App\Models\User;
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
            ->get(route('packages.index', ['name' => 'pkgtest-openssl']))
            ->assertOk()
            ->assertSee('pkgtest-openssl')
            ->assertSee('3.0.2-1')
            ->assertSee('3.0.13')
            ->assertSee('pkg-host-two')
            ->assertDontSee('pkgtest-nginx')
            ->assertViewHas('packages', fn ($packages) => $packages->get('pkgtest-openssl')['device_count'] == 2);
    }

    public function testVersionComparison(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create(['name' => 'pkgtest-cmp', 'version' => '1:2.10.1', 'build' => '', 'arch' => 'all']);
        Package::factory()->for(Device::factory())->create(['name' => 'pkgtest-cmp', 'version' => '1:2.9.0', 'build' => '', 'arch' => 'all']);

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['name' => 'pkgtest-cmp', 'version' => '< 1.2.10']))
            ->assertOk()
            ->assertSee('1:2.9.0')
            ->assertDontSee('1:2.10.1');

        $this->actingAs($this->admin())
            ->get(route('packages.index', ['name' => 'pkgtest-cmp', 'version' => '2.10']))
            ->assertOk()
            ->assertSee('1:2.10.1')
            ->assertDontSee('1:2.9.0');
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
            ->get(route('packages.index', ['name' => 'pkgtest-']))
            ->assertOk()
            ->assertSee('pkgtest-visible')
            ->assertDontSee('pkgtest-hidden');
    }

    public function testLegacyUrlRedirects(): void
    {
        $this->actingAs($this->admin())
            ->get('packages/name=pkgtest-legacy/')
            ->assertRedirect(route('packages.index', ['name' => 'pkgtest-legacy']));
    }
}
