<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\Pseudowire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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
            ->get(route('device.pseudowires', ['device' => $device]))
            ->assertOk()
            ->assertSee('101')
            ->assertSee('Test-PW-101')
            ->assertSee('unresolved remote device')
            ->assertSee('fa-arrow-up tw:text-green-600 tw:dark:text-green-400')
            ->assertDontSee('fa-question tw:text-amber-600');
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
            ->get(route('device.pseudowires', ['device' => $deviceA]))
            ->assertOk()
            ->assertSee('200')
            ->assertSee('Local-PW-200')
            ->assertSee('Remote-PW-200')
            ->assertSee($deviceB->display)
            ->assertSee('fa-arrow-up tw:text-green-600 tw:dark:text-green-400')
            ->assertSee('fa-arrow-down tw:text-red-600 tw:dark:text-red-400')
            ->assertDontSee('fa-question tw:text-amber-600');
    }

    public function testPseudowiresTabMiniGraphsView(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'unknown']);
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'peer_device_id' => 0,
            'cpwVcID' => 101,
            'pw_descr' => 'Test-PW-101',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.pseudowires', ['device' => $device, 'view' => 'minigraphs']))
            ->assertOk()
            ->assertSee('fa-question tw:text-amber-600 tw:dark:text-amber-400')
            ->assertSee('type=port_bits', false)
            ->assertSee('pseudowires?view=detail', false)
            ->assertSee('pseudowires?view=minigraphs', false);
    }

    public function testLegacyPathViewRedirectsToQuery(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'peer_device_id' => 0,
            'cpwVcID' => 101,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'pseudowires', 'vars' => 'view=minigraphs']))
            ->assertRedirect(route('device.pseudowires', ['device' => $device, 'view' => 'minigraphs']));
    }

    public function testInvalidViewIsRejected(): void
    {
        $device = Device::factory()->create();
        Pseudowire::factory()->create(['device_id' => $device->device_id, 'peer_device_id' => 0]);

        $this->actingAs($this->admin())
            ->get(route('device.pseudowires', ['device' => $device, 'view' => 'bogus']))
            ->assertSessionHasErrors('view');
    }

    public function testEmptyViewDefaultsToDetail(): void
    {
        $device = Device::factory()->create();
        Pseudowire::factory()->create(['device_id' => $device->device_id, 'peer_device_id' => 0]);

        $this->actingAs($this->admin())
            ->get(route('device.pseudowires', ['device' => $device]) . '?view=')
            ->assertOk()
            ->assertViewHas('view', 'detail');
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
            ->get(route('device.pseudowires', ['device' => $device]))
            ->assertForbidden();
    }

    #[DataProvider('peerResolution')]
    public function testGlobalPageListsEachPairOnceWithDevices(bool $bothPeersResolved): void
    {
        $deviceA = Device::factory()->create(['hostname' => 'router-a.example.com']);
        $portA = Port::factory()->for($deviceA)->create(['ifOperStatus' => 'up']);
        $deviceB = Device::factory()->create(['hostname' => 'router-b.example.com']);
        $portB = Port::factory()->for($deviceB)->create(['ifOperStatus' => 'up']);

        foreach ([[$deviceA, $portA, $deviceB, 'Side-A'], [$deviceB, $portB, $deviceA, 'Side-B']] as [$device, $port, $peer, $descr]) {
            Pseudowire::factory()->for($device)->for($port)->create([
                'peer_device_id' => $bothPeersResolved || $device->is($deviceB) ? $peer->device_id : 0,
                'cpwVcID' => 300,
                'pw_descr' => $descr,
            ]);
        }

        $response = $this->actingAs($this->admin())
            ->get(route('pseudowires', ['view' => 'minigraphs']))
            ->assertOk()
            ->assertSee('router-a.example.com')
            ->assertSee('router-b.example.com')
            ->assertSee('alt="port_upkts"', false)
            ->assertSee('alt="port_errors"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'Side-A'));
        $this->assertSame(1, substr_count($response->getContent(), 'Side-B'));
    }

    /** @return array<string, array{bool}> */
    public static function peerResolution(): array
    {
        return [
            'both endpoints resolved' => [true],
            'one endpoint resolved' => [false],
        ];
    }

    public function testPseudowiresTabIgnoresAnUnrelatedCircuitWithTheSameVcId(): void
    {
        [$device, $peer, $otherDevice] = Device::factory()->count(3)->create()->all();

        Pseudowire::factory()->for($device)->for(Port::factory()->for($device))->create([
            'peer_device_id' => $peer->device_id,
            'cpwVcID' => 400,
            'pw_descr' => 'Local circuit',
        ]);
        Pseudowire::factory()->for($peer)->for(Port::factory()->for($peer))->create([
            'peer_device_id' => $otherDevice->device_id,
            'cpwVcID' => 400,
            'pw_descr' => 'Unrelated circuit',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.pseudowires', ['device' => $device]))
            ->assertOk()
            ->assertSee('Local circuit')
            ->assertDontSee('Unrelated circuit');
    }

    public function testGlobalPageOnlyShowsAccessiblePseudowires(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Pseudowire::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'pw_descr' => 'Hidden-PW',
        ]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('pseudowires'))
            ->assertOk()
            ->assertDontSee('Hidden-PW')
            ->assertSee('No pseudowires found.');
    }
}
