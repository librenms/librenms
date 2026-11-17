<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\User;
use App\Models\UserPref;
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
            ->followingRedirects()
            ->get(route('device', [
                'device' => $device,
                'tab' => 'port',
                'vars' => 'port=' . $port->port_id . '/view=notes/graph=bits/subview=graphs',
            ]))
            ->assertOk()
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id]), false);
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
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'port' => $portWithEvents->port_id]))
            ->assertDontSee(route('device.eventlog', ['device' => $device->device_id, 'port' => $portWithoutEvents->port_id]));
    }

    public function testOldPortEventsViewFallsBackToGraphs(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1', 'ifOperStatus' => 'up']);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->get(route('device', [
                'device' => $device,
                'tab' => 'port',
                'vars' => 'port=' . $port->port_id . '/view=events',
            ]))
            ->assertOk()
            ->assertSee(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id]), false);
    }

    public function testEventlogTableFiltersByPort(): void
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
                'port' => $port->port_id,
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.message', 'ifOperStatus: up -&gt; down');
    }

    public function testEventlogTablePortFilterIgnoresOtherTypesWithSameReference(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifDescr' => 'GigabitEthernet0/1']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => (string) $port->port_id,
            'message' => 'port event',
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'temperature',
            'reference' => (string) $port->port_id,
            'message' => 'sensor event',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('table.eventlog'), [
                'device' => $device->device_id,
                'port' => $port->port_id,
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.message', 'port event');
    }

    public function testExplicitFiltersOverrideThePortShortcut(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        $admin = $this->admin();
        $savedFilter = ['type' => ['eq' => 'system']];
        $urlFilter = ['search' => ['eq' => 'ifSpeed changed']];
        UserPref::setPref($admin, 'filters.device.eventlog', $savedFilter);

        $this->actingAs($admin)
            ->get(route('device.eventlog', ['device' => $device->device_id]))
            ->assertOk()
            ->assertViewHas('filter', $savedFilter);

        $this->actingAs($admin)
            ->get(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id]))
            ->assertOk()
            ->assertViewHas('filter', ['port' => ['eq' => $port->port_id]]);

        $this->actingAs($admin)
            ->get(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id, 'filter' => $urlFilter]))
            ->assertOk()
            ->assertViewHas('filter', $urlFilter);

        $this->actingAs($admin)
            ->get(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id, 'filter' => '']))
            ->assertOk()
            ->assertViewHas('filter', []);
    }

    public function testFilterBarPortAndSearch(): void
    {
        $device = Device::factory()->createQuietly();
        $port = Port::factory()->for($device)->create(['ifName' => 'uplink-port', 'ifDescr' => 'uplink-port', 'ifAlias' => '']);
        Eventlog::factory()->create(['device_id' => $device->device_id, 'type' => 'interface', 'reference' => $port->port_id, 'message' => 'ifSpeed changed']);
        Eventlog::factory()->create(['device_id' => $device->device_id, 'type' => 'system', 'reference' => $port->port_id, 'message' => 'not a port event']);
        Eventlog::factory()->create(['device_id' => $device->device_id, 'type' => 'system', 'message' => 'uplink-port mentioned']);
        Eventlog::factory()->create(['device_id' => $device->device_id, 'type' => null, 'reference' => $port->port_id, 'message' => 'uncategorized event']);

        $post = fn (array $filter) => $this->actingAs($this->admin())->postJson(route('table.eventlog'), [
            'current' => 1,
            'rowCount' => 50,
            'device' => $device->device_id,
            'filter' => $filter,
        ])->assertOk()->json('rows.*.message');

        $this->assertSame(['ifSpeed changed'], $post(['port' => ['eq' => $port->port_id]]));
        $this->assertEqualsCanonicalizing(['not a port event', 'uplink-port mentioned', 'uncategorized event'], $post(['port' => ['neq' => $port->port_id]]));
        $this->assertSame(['ifSpeed changed'], $post(['search' => ['eq' => 'uplink-port']]));
        $this->assertEqualsCanonicalizing(['ifSpeed changed', 'uplink-port mentioned'], $post(['search' => ['contains' => 'uplink']]));
        $this->assertEqualsCanonicalizing(['not a port event', 'uncategorized event'], $post(['search' => ['not_contains' => 'uplink']]));
        $this->assertEqualsCanonicalizing(['ifSpeed changed', 'uplink-port mentioned'], $post(['search' => ['in' => ['uplink-port', 'uplink-port mentioned']]]));
        $this->assertEqualsCanonicalizing(['not a port event', 'uncategorized event'], $post(['search' => ['not_in' => 'uplink-port,uplink-port mentioned']]));
        $this->assertSame([], $post(['search' => ['is_empty' => '']]));
    }

    public function testEventlogPageEscapesHtmlInPortLabel(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifName' => 'evil <b>bold</b>', 'ifDescr' => 'evil <b>bold</b>', 'ifOperStatus' => 'up']);

        $html = $this->actingAs($this->admin())
            ->get(route('device.eventlog', ['device' => $device->device_id, 'port' => $port->port_id]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<b>bold</b>', $html);
    }
}
