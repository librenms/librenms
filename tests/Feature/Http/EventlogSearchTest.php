<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class EventlogSearchTest extends TestCase
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

    public function testEventlogTableSearchMatchesPortLabel(): void
    {
        $device = Device::factory()->create();
        $portA = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/1', 'ifDescr' => 'GigabitEthernet0/1']);
        $portB = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/2', 'ifDescr' => 'GigabitEthernet0/2']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => (string) $portA->port_id,
            'message' => 'ifOperStatus: up -> down',
        ]);
        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => (string) $portB->port_id,
            'message' => 'ifOperStatus: down -> up',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('table.eventlog'), [
                'device' => $device->device_id,
                'eventtype' => 'interface',
                'searchPhrase' => 'GigabitEthernet0/1',
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.message', 'ifOperStatus: up -&gt; down');
    }

    public function testEventlogSearchDoesNotMatchNonInterfaceEventsWithPortIdReference(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/1', 'ifDescr' => 'GigabitEthernet0/1']);

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'system',
            'reference' => (string) $port->port_id,
            'message' => 'system event with a port id reference',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('table.eventlog'), [
                'device' => $device->device_id,
                'searchPhrase' => 'GigabitEthernet0/1',
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(0, 'rows');
    }

    public function testEventlogSearchStillMatchesMessage(): void
    {
        $device = Device::factory()->create();

        Eventlog::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'system',
            'message' => 'unique-marker-message',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('table.eventlog'), [
                'device' => $device->device_id,
                'searchPhrase' => 'unique-marker-message',
                'rowCount' => 25,
                'current' => 1,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.message', 'unique-marker-message');
    }
}
