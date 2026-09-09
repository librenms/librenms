<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\PrinterSupply;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PrinterTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderPrinterTab(): void
    {
        $device = Device::factory()->create();
        PrinterSupply::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'printer']))
            ->assertOk()
            ->assertSee('Toner')
            ->assertSee('device_toner')
            ->assertSee('legend=yes');
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        PrinterSupply::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'printer']))
            ->assertForbidden();
    }
}
