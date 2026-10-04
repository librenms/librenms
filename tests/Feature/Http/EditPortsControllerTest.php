<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditPortsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        LibrenmsConfig::set('polling.selected_ports', false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function port(Device $device, array $attributes = []): Port
    {
        return Port::factory()->for($device)->create(array_merge([
            'ifName' => 'eth' . fake()->unique()->numberBetween(0, 10000),
            'ifDescr' => 'Ethernet',
            'ifAlias' => 'Uplink',
            'ifSpeed' => 1000000000,
            'ifAdminStatus' => 'up',
            'ifOperStatus' => 'up',
            'disabled' => 0,
            'ignore' => 0,
            'deleted' => 0,
        ], $attributes));
    }

    /**
     * @return array{up: Port, down: Port, admin_down: Port, lower_layer_down: Port, disabled: Port, deleted: Port}
     */
    private function portSet(Device $device): array
    {
        return [
            'up' => $this->port($device, ['ifIndex' => 1]),
            'down' => $this->port($device, ['ifIndex' => 2, 'ifOperStatus' => 'down']),
            'admin_down' => $this->port($device, ['ifIndex' => 3, 'ifAdminStatus' => 'down', 'ifOperStatus' => 'down']),
            'lower_layer_down' => $this->port($device, ['ifIndex' => 4, 'ifOperStatus' => 'lowerLayerDown']),
            'disabled' => $this->port($device, ['ifIndex' => 5, 'disabled' => 1]),
            'deleted' => $this->port($device, ['ifIndex' => 6, 'deleted' => 1]),
        ];
    }

    /**
     * @param  array<string, string>  $query
     * @return array<int, string>
     */
    private function pollingStates(Device $device, array $query = []): array
    {
        $response = $this->actingAs($this->admin())
            ->getJson(route('device.edit.ports.list', $device) . '?' . http_build_query($query))
            ->assertOk();

        return array_column($response->json('ports'), 'polling', 'ifIndex');
    }

    public function testAdminCanViewPortsPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.edit.ports', $device))
            ->assertOk()
            ->assertSee('Selected port polling')
            ->assertSee('devicePorts', false);
    }

    public function testUserCannotViewPortsPage(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device);

        $this->actingAs($this->user())
            ->get(route('device.edit.ports', $device))
            ->assertForbidden();

        $this->actingAs($this->user())
            ->getJson(route('device.edit.ports.list', $device))
            ->assertForbidden();

        $this->actingAs($this->user())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['disabled' => true])
            ->assertForbidden();

        $this->assertSame(0, $port->fresh()->disabled);
    }

    public function testListReturnsStructuredData(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device, ['ifName' => 'Gi0/1', 'ifAlias' => '<b>uplink</b>']);
        $device->setAttrib('ifName:Gi0/1', 1);

        $response = $this->actingAs($this->admin())
            ->getJson(route('device.edit.ports.list', $device))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('ports.0.port_id', $port->port_id)
            ->assertJsonPath('ports.0.ifAlias', '<b>uplink</b>')
            ->assertJsonPath('ports.0.ifAlias_override', true)
            ->assertJsonPath('ports.0.ifSpeed_override', false)
            ->assertJsonPath('ports.0.polling', 'polled');

        $this->assertArrayHasKey('summary', $response->json());
    }

    public function testFullPollingPollsDownPorts(): void
    {
        $device = Device::factory()->create();
        $this->portSet($device);

        $this->assertSame([
            1 => 'polled',
            2 => 'polled',
            3 => 'polled',
            4 => 'polled',
            5 => 'disabled',
            6 => 'deleted',
        ], $this->pollingStates($device));
    }

    public function testSelectedPortPollingSkipsDownPorts(): void
    {
        $device = Device::factory()->create();
        $this->portSet($device);
        $device->setAttrib('selected_ports', 'true');

        $this->assertSame([
            1 => 'polled',
            2 => 'down',
            3 => 'admin_down',
            4 => 'down',
            5 => 'disabled',
            6 => 'deleted',
        ], $this->pollingStates($device));

        $this->assertSame([1 => 'polled'], $this->pollingStates($device, ['filter' => 'polled']));
        $this->assertSame([2, 3, 4], array_keys($this->pollingStates($device, ['filter' => 'skipped'])));
        $this->assertSame([2, 3, 4, 5, 6], array_keys($this->pollingStates($device, ['filter' => 'not_polled'])));

        $this->actingAs($this->admin())
            ->getJson(route('device.edit.ports.list', $device))
            ->assertJsonPath('summary', [
                'total' => 6,
                'polled' => 1,
                'skipped' => 3,
                'disabled' => 1,
                'deleted' => 1,
                'ignored' => 0,
            ]);
    }

    public function testSelectedPortPollingFallsBackToOsThenGlobal(): void
    {
        $device = Device::factory()->create(['os' => 'linux']);
        $this->port($device, ['ifIndex' => 1, 'ifOperStatus' => 'down']);

        LibrenmsConfig::set('polling.selected_ports', true);
        $this->assertSame([1 => 'down'], $this->pollingStates($device));

        LibrenmsConfig::set('os.linux.polling.selected_ports', false);
        $this->assertSame([1 => 'polled'], $this->pollingStates($device));

        $device->setAttrib('selected_ports', 'true');
        $this->assertSame([1 => 'down'], $this->pollingStates($device));

        LibrenmsConfig::forget('os.linux.polling.selected_ports');
    }

    public function testSearchAndFilters(): void
    {
        $device = Device::factory()->create();
        $this->port($device, ['ifIndex' => 10, 'ifName' => 'Gi0/10', 'ifAlias' => 'core uplink']);
        $this->port($device, ['ifIndex' => 11, 'ifName' => 'Gi0/11', 'ifAlias' => 'access', 'ifOperStatus' => 'down']);
        $this->port($device, ['ifIndex' => 12, 'ifName' => 'Gi0/12', 'ifAlias' => 'access', 'ignore' => 1]);
        $this->port(Device::factory()->create(), ['ifIndex' => 13, 'ifName' => 'Gi0/13', 'ifAlias' => 'core uplink']);

        $this->assertSame([10], array_keys($this->pollingStates($device, ['search' => 'core'])));
        $this->assertSame([11], array_keys($this->pollingStates($device, ['search' => '11'])));
        $this->assertSame([11], array_keys($this->pollingStates($device, ['filter' => 'down'])));
        $this->assertSame([12], array_keys($this->pollingStates($device, ['filter' => 'ignored'])));
        $this->assertSame([12, 11, 10], array_keys($this->pollingStates($device, ['sort' => 'ifIndex', 'order' => 'desc'])));

        $this->actingAs($this->admin())
            ->getJson(route('device.edit.ports.list', $device) . '?filter=bogus')
            ->assertUnprocessable();
    }

    public function testPagination(): void
    {
        $device = Device::factory()->create();
        foreach (range(1, 5) as $ifIndex) {
            $this->port($device, ['ifIndex' => $ifIndex]);
        }

        $this->actingAs($this->admin())
            ->getJson(route('device.edit.ports.list', $device) . '?per_page=2&page=3')
            ->assertOk()
            ->assertJsonPath('total', 5)
            ->assertJsonPath('page', 3)
            ->assertJsonPath('last_page', 3)
            ->assertJsonCount(1, 'ports')
            ->assertJsonPath('ports.0.ifIndex', 5);
    }

    public function testUpdateToggles(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device, ['ifName' => 'eth0']);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['disabled' => true, 'ignore' => true])
            ->assertOk()
            ->assertJsonPath('port.disabled', true)
            ->assertJsonPath('port.ignore', true)
            ->assertJsonPath('port.polling', 'disabled')
            ->assertJsonPath('summary.disabled', 1);

        $port->refresh();
        $this->assertSame(1, $port->disabled);
        $this->assertSame(1, $port->ignore);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['rrd_tune' => true])
            ->assertOk()
            ->assertJsonPath('port.rrd_tune', true);
        $this->assertSame('true', $device->getAttrib('ifName_tune:eth0'));

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['rrd_tune' => false])
            ->assertOk()
            ->assertJsonPath('port.rrd_tune', false);
        $this->assertSame('false', $device->fresh()->getAttrib('ifName_tune:eth0'));
    }

    public function testUpdateAlias(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device, ['ifName' => 'eth0']);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['ifAlias' => 'To core'])
            ->assertOk()
            ->assertJsonPath('port.ifAlias', 'To core')
            ->assertJsonPath('port.ifAlias_override', true);

        $this->assertSame('To core', $port->fresh()->ifAlias);
        $this->assertNotNull($device->fresh()->getAttrib('ifName:eth0'));

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['ifAlias' => ''])
            ->assertOk()
            ->assertJsonPath('port.ifAlias', '')
            ->assertJsonPath('port.ifAlias_override', false);

        $this->assertSame('repoll', $port->fresh()->ifAlias);
        $this->assertNull($device->fresh()->getAttrib('ifName:eth0'));
    }

    public function testUpdateSpeed(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device, ['ifName' => 'eth0']);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['ifSpeed' => 10000000])
            ->assertOk()
            ->assertJsonPath('port.ifSpeed', 10000000)
            ->assertJsonPath('port.ifSpeed_override', true);

        $this->assertSame(10000000, $port->fresh()->ifSpeed);
        $this->assertEquals(10000000, $device->fresh()->getAttrib('ifSpeed:eth0'));

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['ifSpeed' => null])
            ->assertOk()
            ->assertJsonPath('port.ifSpeed_override', false);

        $this->assertNull($device->fresh()->getAttrib('ifSpeed:eth0'));

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), ['ifSpeed' => 'fast'])
            ->assertUnprocessable();
    }

    public function testInvalidUpdatesAreRejected(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device);
        $otherPort = $this->port(Device::factory()->create());

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $port]), [])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.ports.update', [$device, $otherPort]), ['disabled' => true])
            ->assertNotFound();

        $this->assertSame(0, $otherPort->fresh()->disabled);
    }

    public function testBulkActionAppliesToFilteredPorts(): void
    {
        $device = Device::factory()->create();
        $up = $this->port($device, ['ifIndex' => 1]);
        $down = $this->port($device, ['ifIndex' => 2, 'ifOperStatus' => 'down']);
        $otherDevicePort = $this->port(Device::factory()->create(), ['ifIndex' => 3, 'ifOperStatus' => 'down']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.ports.bulk', $device), ['action' => 'ignore', 'filter' => 'down'])
            ->assertOk()
            ->assertJsonPath('updated', 1)
            ->assertJsonPath('summary.ignored', 1);

        $this->assertSame(0, $up->fresh()->ignore);
        $this->assertSame(1, $down->fresh()->ignore);
        $this->assertSame(0, $otherDevicePort->fresh()->ignore);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.ports.bulk', $device), ['action' => 'disable'])
            ->assertOk()
            ->assertJsonPath('updated', 2);

        $this->assertSame(1, $up->fresh()->disabled);
        $this->assertSame(1, $down->fresh()->disabled);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.ports.bulk', $device), ['action' => 'explode'])
            ->assertUnprocessable();

        $this->actingAs($this->user())
            ->postJson(route('device.edit.ports.bulk', $device), ['action' => 'enable'])
            ->assertForbidden();
    }

    public function testSelectedPortsSetting(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.ports.settings', $device), ['selected_ports' => 'true'])
            ->assertOk()
            ->assertJsonPath('selected_ports.device', true);
        $this->assertSame('true', $device->fresh()->getAttrib('selected_ports'));

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.ports.settings', $device), ['selected_ports' => 'false'])
            ->assertOk()
            ->assertJsonPath('selected_ports.device', false);
        $this->assertSame('false', $device->fresh()->getAttrib('selected_ports'));

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.ports.settings', $device), ['selected_ports' => 'clear'])
            ->assertOk()
            ->assertJsonPath('selected_ports.device', null);
        $this->assertNull($device->fresh()->getAttrib('selected_ports'));

        $this->actingAs($this->admin())
            ->putJson(route('device.edit.ports.settings', $device), ['selected_ports' => 'maybe'])
            ->assertUnprocessable();

        $this->actingAs($this->user())
            ->putJson(route('device.edit.ports.settings', $device), ['selected_ports' => 'true'])
            ->assertForbidden();
    }

    public function testResetPortState(): void
    {
        $device = Device::factory()->create();
        $port = $this->port($device, [
            'ifSpeed_prev' => 100,
            'ifOperStatus_prev' => 'down',
            'ifAdminStatus_prev' => 'down',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.ports.reset-state', $device))
            ->assertOk();

        $port->refresh();
        $this->assertNull($port->ifSpeed_prev);
        $this->assertNull($port->ifOperStatus_prev);
        $this->assertNull($port->ifAdminStatus_prev);
    }

    public function testMiscPageNoLongerHasSelectedPorts(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.edit.misc', $device))
            ->assertOk()
            ->assertDontSee('selected_ports');
    }
}
