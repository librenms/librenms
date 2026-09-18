<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PackagesTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderPackagesTab(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create([
            'name' => 'nginx-core',
            'version' => '1.24.0',
            'build' => '1ubuntu1',
            'arch' => 'amd64',
            'manager' => 'deb',
            'size' => 1048576,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'packages']))
            ->assertOk()
            ->assertSee('nginx-core')
            ->assertSee('1.24.0-1ubuntu1')
            ->assertSee('amd64')
            ->assertSee('deb')
            ->assertSee(\LibreNMS\Util\Number::formatSi(1048576, 2, 0, ''));
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'packages']))
            ->assertForbidden();
    }
}
