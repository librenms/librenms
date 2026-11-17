<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortVlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Url;
use Spatie\Permission\Models\Role;

final class PortTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testGraphsViewAndMenu(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id]))
            ->assertOk()
            ->assertSee('Real time')
            ->assertSee('Eventlog')
            ->assertSee('Notes');
    }

    public function testUnknownViewFallsBackToGraphs(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => '../../etc']))
            ->assertOk()
            ->assertSee('<span class="pagemenu-selected">', false);
    }

    public function testNotesViewAndSave(): void
    {
        $port = $this->port();
        $port->device->setAttrib("port_id_notes:$port->port_id", 'uplink <a>');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'notes']))
            ->assertOk()
            ->assertSee('Notes*')
            ->assertDontSee('uplink <a>', false);

        $this->actingAs($admin)
            ->putJson(route('port.notes.update', $port), ['notes' => 'changed'])
            ->assertOk();
        $this->assertSame('changed', $port->device->fresh()->getAttrib("port_id_notes:$port->port_id"));
    }

    public function testVlansListOtherPorts(): void
    {
        $port = $this->port(['ifIndex' => 1]);
        $device = $port->device;
        $other = Port::factory()->create(['device_id' => $device->device_id, 'ifIndex' => 2, 'ifDescr' => 'eth2', 'ifVlan' => 10]);
        $device->vlans()->create(['vlan_vlan' => 10, 'vlan_name' => 'Users <10>', 'vlan_domain' => 1]);
        PortVlan::create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'vlan' => 10, 'state' => 'forwarding', 'untagged' => 0, 'baseport' => 1, 'priority' => 0, 'cost' => 4]);

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'vlans']))
            ->assertOk()
            ->assertSee('Users &lt;10&gt;', false)
            ->assertSee('eth2')
            ->assertSee('(U)');
    }

    public function testPortUrlUsesPortIdPathAndQueryOptions(): void
    {
        $port = $this->port();

        $this->assertSame(
            url("device/$port->device_id/port/$port->port_id") . '?view=realtime&interval=5',
            Url::portUrl($port, ['view' => 'realtime', 'interval' => '5', 'empty' => ''])
        );
    }

    public function testMenuLinksUseQueryString(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id]))
            ->assertOk()
            ->assertSee("port/$port->port_id?view=realtime", false)
            ->assertDontSee("/port=$port->port_id", false);
    }

    public function testRealtimeIntervalFromQuery(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'realtime', 'interval' => '60']))
            ->assertOk()
            ->assertSee('view=realtime&amp;interval=60', false)
            ->assertViewHas('view', 'realtime')
            ->assertViewHas('viewData', fn ($viewData) => $viewData['interval'] == '60');
    }

    public function testLegacyPathOptionsRedirectToQueryKeepingPortId(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get('/device/device=' . $port->device_id . "/tab=port/port=$port->port_id/view=notes/")
            ->assertRedirect(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'notes']));
    }

    public function testLegacyPortIdRedirectsToNativePath(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get("/device/$port->device_id/port/port=$port->port_id/")
            ->assertStatus(301)
            ->assertRedirect(url("device/$port->device_id/port/$port->port_id"));
    }

    public function testLegacyDeviceSegmentRedirects(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get("/device/device=$port->device_id/port/$port->port_id")
            ->assertRedirect(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id]));
    }

    public function testPortOfAnotherDeviceIsNotFound(): void
    {
        $port = $this->port();
        $other = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get("/device/$other->device_id/port/$port->port_id")
            ->assertNotFound();
    }

    public function testLegacyPathRedirectRequiresPortAccess(): void
    {
        $port = $this->port();

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->get(route('device', ['device' => $port->device_id, 'tab' => 'port', 'vars' => "port=$port->port_id/view=notes"]))
            ->assertForbidden();
    }

    public function testInvalidQosShowIsRejected(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'qos', 'show' => 'abc']))
            ->assertSessionHasErrors('show');
    }

    public function testPortIdIsNotReadFromQuery(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $port->device_id, 'tab' => 'port', 'port' => $port->port_id]))
            ->assertNotFound();
    }

    public function testArrayOptionIsRejected(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => ['notes']]))
            ->assertSessionHasErrors('view');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function port(array $attributes = []): Port
    {
        $device = Device::factory()->create();

        return Port::factory()->create(['device_id' => $device->device_id, 'ifDescr' => 'eth1'] + $attributes);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
