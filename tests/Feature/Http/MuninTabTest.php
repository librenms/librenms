<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\MuninPlugin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class MuninTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderMuninTab(): void
    {
        $device = Device::factory()->create();
        MuninPlugin::factory()->for($device)->create([
            'mplug_category' => 'system',
            'mplug_type' => 'cpu',
            'mplug_title' => 'CPU Usage',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'munin']))
            ->assertOk()
            ->assertSee('Munin Plugins')
            ->assertSee('System')
            ->assertSee('CPU Usage');
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        MuninPlugin::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'munin']))
            ->assertForbidden();
    }
}
