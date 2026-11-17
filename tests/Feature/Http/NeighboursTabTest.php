<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class NeighboursTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testListIsDefaultAndLinksUseQuery(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'neighbours']))
            ->assertOk()
            ->assertSee('neighbour.example.com')
            ->assertSee('neighbours?selection=list', false)
            ->assertSee('neighbours?selection=map', false);
    }

    public function testMapSelectionFromQuery(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'neighbours', 'selection' => 'map']))
            ->assertOk()
            ->assertDontSee('neighbour.example.com')
            ->assertSee('neighbours?selection=map', false);
    }

    public function testLegacyPathSelectionRedirectsToQuery(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'neighbours', 'vars' => 'selection=map']))
            ->assertRedirect(route('device', ['device' => $device, 'tab' => 'neighbours', 'selection' => 'map']));
    }

    public function testInvalidSelectionIsRejected(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'neighbours', 'selection' => 'bogus']))
            ->assertSessionHasErrors('selection');
    }

    private function device(): Device
    {
        $device = Device::factory()->create();
        Link::factory()->create([
            'local_device_id' => $device->device_id,
            'local_port_id' => 0,
            'remote_device_id' => 0,
            'remote_port_id' => 0,
            'remote_hostname' => 'neighbour.example.com',
        ]);

        return $device;
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
