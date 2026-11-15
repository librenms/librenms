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
            ->get(Url::portUrl($port))
            ->assertOk()
            ->assertSee('Real time')
            ->assertSee('Eventlog')
            ->assertSee('Notes');
    }

    public function testUnknownViewFallsBackToGraphs(): void
    {
        $port = $this->port();

        $this->actingAs($this->admin())
            ->get(Url::portUrl($port, ['view' => '../../etc']))
            ->assertOk()
            ->assertSee('<span class="pagemenu-selected">', false);
    }

    public function testNotesViewAndSave(): void
    {
        $port = $this->port();
        $port->device->setAttrib("port_id_notes:$port->port_id", 'uplink <a>');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(Url::portUrl($port, ['view' => 'notes']))
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
            ->get(Url::portUrl($port, ['view' => 'vlans']))
            ->assertOk()
            ->assertSee('Users &lt;10&gt;', false)
            ->assertSee('eth2')
            ->assertSee('(U)');
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
