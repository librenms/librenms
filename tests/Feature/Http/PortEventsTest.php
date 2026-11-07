<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PortEventsTest extends TestCase
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

    /**
     * @return array{0: Device, 1: Port, 2: Port}
     */
    private function fixture(): array
    {
        $device = Device::factory()->create(['hostname' => 'router-a.example.com']);
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);
        $otherPort = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/2', 'ifOperStatus' => 'down']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'message' => 'Interface up',
            'type' => 'interface',
            'reference' => $port->port_id,
            'severity' => Severity::Info,
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'message' => 'Interface down',
            'type' => 'interface',
            'reference' => $port->port_id,
            'severity' => Severity::Warning,
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'message' => 'Other port event',
            'type' => 'interface',
            'reference' => $otherPort->port_id,
            'severity' => Severity::Info,
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'message' => 'System event',
            'type' => 'system',
            'reference' => $port->port_id,
            'severity' => Severity::Notice,
        ]);

        return [$device, $port, $otherPort];
    }

    public function testAuthorizedUserCanRenderPortEventsPage(): void
    {
        [$device, $port] = $this->fixture();

        $this->actingAs($this->admin())
            ->get(route('port.events', ['port' => $port]))
            ->assertOk()
            ->assertSee('Interface up')
            ->assertSee('Interface down')
            ->assertSee('router-a.example.com')
            ->assertSee('label-info')
            ->assertSee('label-warning')
            ->assertDontSee('Other port event')
            ->assertDontSee('System event');
    }

    public function testLegacyPortEventsUrlShowsShimLinkingToModernPage(): void
    {
        [$device, $port] = $this->fixture();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'port', 'vars' => 'port=' . $port->port_id . '/view=events/graph=bits/subview=graphs']))
            ->assertOk()
            ->assertSee(route('port.events', ['port' => $port]))
            ->assertDontSee('Interface up');
    }

    public function testUserWithoutDeviceAccessGetsForbidden(): void
    {
        [$device, $port] = $this->fixture();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('port.events', ['port' => $port]))
            ->assertForbidden();
    }
}
