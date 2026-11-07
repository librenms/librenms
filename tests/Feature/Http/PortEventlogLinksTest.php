<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PortEventlogLinksTest extends TestCase
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

    public function testPortPageMenuLinksToPortFilteredEventlog(): void
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
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface', 'reference' => $port->port_id]), false);
    }

    public function testPortListShowsEventlogLinkOnlyForPortsWithEvents(): void
    {
        $device = Device::factory()->create();
        $portWithEvents = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/1', 'ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);
        $portWithoutEvents = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/2', 'ifDescr' => 'GigabitEthernet0/2', 'ifOperStatus' => 'up']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => (string) $portWithEvents->port_id,
            'message' => 'ifOperStatus: up -> down',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'ports']))
            ->assertOk()
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface', 'reference' => $portWithEvents->port_id]))
            ->assertDontSee(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface', 'reference' => $portWithoutEvents->port_id]));
    }

    public function testEventlogTableFiltersByPortReference(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => (string) $port->port_id,
            'message' => 'ifOperStatus: up -> down',
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => '999999',
            'message' => 'ifOperStatus: down -> up',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('table.eventlog'), [
                'device' => $device->device_id,
                'eventtype' => 'interface',
                'reference' => $port->port_id,
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.message', 'ifOperStatus: up -&gt; down');
    }

    public function testEventlogPageShowsPortLabelAndReferencePassThrough(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/1', 'ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);

        $response = $this->actingAs($this->admin())
            ->get(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface', 'reference' => $port->port_id]));
        $response->assertOk()
            ->assertSee('GigabitEthernet0', false)
            ->assertSee('name="reference"', false)
            ->assertSee('value="' . $port->port_id . '"', false);

        $html = $response->getContent();
        $this->assertNotFalse(mb_strpos($html, 'Filter</button>'));
        $this->assertNotFalse(mb_strpos($html, 'Port&nbsp;&nbsp;'));
        $this->assertTrue(mb_strpos($html, 'Filter</button>') < mb_strpos($html, 'Port&nbsp;&nbsp;'));
    }

    public function testEventlogPageEscapesHtmlInPortLabel(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifName' => 'evil <b>bold</b>', 'ifDescr' => 'evil <b>bold</b>', 'ifOperStatus' => 'up']);

        $html = $this->actingAs($this->admin())
            ->get(route('device.eventlog', ['device' => $device->device_id, 'eventtype' => 'interface', 'reference' => $port->port_id]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<b>bold</b>', $html);
        $this->assertStringNotContainsString('\u003Cb\u003E', $html);
        $this->assertStringContainsString('\u0026lt;b\u0026gt;bold', $html);
    }
}
