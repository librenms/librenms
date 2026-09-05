<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\Pseudowire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PseudowiresTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderPseudowiresTabWithUnresolvedPeer(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'peer_device_id' => 0,
            'cpwVcID' => 101,
            'pw_descr' => 'Test-PW-101',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'pseudowires']))
            ->assertOk()
            ->assertSee('101')
            ->assertSee('Test-PW-101')
            ->assertSee('unresolved remote device')
            ->assertSee('fa-arrow-up report-up')
            ->assertDontSee('fa-question report-warning');
    }

    public function testPseudowiresTabWithResolvedPeerEndpoint(): void
    {
        $deviceA = Device::factory()->create(['hostname' => 'router-a.example.com']);
        $portA = Port::factory()->for($deviceA)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);

        $deviceB = Device::factory()->create(['hostname' => 'router-b.example.com']);
        $portB = Port::factory()->for($deviceB)->create(['ifDescr' => 'GigabitEthernet0/2', 'ifOperStatus' => 'down']);

        Pseudowire::factory()->create([
            'device_id' => $deviceA->device_id,
            'port_id' => $portA->port_id,
            'peer_device_id' => $deviceB->device_id,
            'cpwVcID' => 200,
            'pw_descr' => 'Local-PW-200',
        ]);

        Pseudowire::factory()->create([
            'device_id' => $deviceB->device_id,
            'port_id' => $portB->port_id,
            'peer_device_id' => $deviceA->device_id,
            'cpwVcID' => 200,
            'pw_descr' => 'Remote-PW-200',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $deviceA, 'tab' => 'pseudowires']))
            ->assertOk()
            ->assertSee('200')
            ->assertSee('Local-PW-200')
            ->assertSee('Remote-PW-200')
            ->assertSee($deviceB->display)
            ->assertSee('fa-arrow-up report-up')
            ->assertSee('fa-arrow-down report-down')
            ->assertDontSee('fa-question report-warning');
    }

    public function testPseudowiresTabMiniGraphsView(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'peer_device_id' => 0,
            'cpwVcID' => 101,
            'pw_descr' => 'Test-PW-101',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'pseudowires', 'vars' => 'view=minigraphs']))
            ->assertOk()
            ->assertSee('type=port_bits', false);
    }

    public function testUserWithoutDeviceAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
        ]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'pseudowires']))
            ->assertForbidden();
    }
}
