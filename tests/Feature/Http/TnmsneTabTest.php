<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\TnmsneInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class TnmsneTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderTnmsneTab(): void
    {
        $device = Device::factory()->create(['os' => 'coriant']);
        TnmsneInfo::factory()->for($device)->create([
            'neName' => 'Coriant-Node-Alpha',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'tnmsne']))
            ->assertOk()
            ->assertSee('Coriant NE Hardware')
            ->assertSee('tnmsne')
            ->assertSee(route('table.tnmsne'));
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create(['os' => 'coriant']);
        TnmsneInfo::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'tnmsne']))
            ->assertForbidden();
    }
}
