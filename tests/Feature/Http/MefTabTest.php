<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\MefInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class MefTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderMefTab(): void
    {
        $device = Device::factory()->create();
        MefInfo::factory()->for($device)->create([
            'mefIdent' => 'EPL-Cust-101',
            'mefType' => 'epl',
            'mefMTU' => 9000,
            'mefAdmState' => 'unlocked',
            'mefRowState' => 'active',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'mef']))
            ->assertOk()
            ->assertSee('EPL-Cust-101')
            ->assertSee('epl')
            ->assertSee('9000')
            ->assertSee('fa-unlock')
            ->assertSee('active');
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        MefInfo::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'mef']))
            ->assertForbidden();
    }
}
