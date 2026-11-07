<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PortEventlogMenuTest extends TestCase
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

    public function testPortMenuLinksToEventlogTableWithInterfaceFilter(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);

        $this->actingAs($this->admin())
            ->get(route('device', [
                'device' => $device,
                'tab' => 'port',
                'vars' => 'port=' . $port->port_id . '/view=notes/graph=bits/subview=graphs',
            ]))
            ->assertOk()
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface']), false);
    }
}
