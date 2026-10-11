<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditRoutingControllerTest extends TestCase
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

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    public function testAdminCanViewRoutingPage(): void
    {
        $device = Device::factory()->create();
        $device->setAttrib('routing_snmp_contexts', json_encode(['vrf-red']));
        BgpPeer::factory()->for($device)->create([
            'bgpPeerIdentifier' => '192.0.2.1',
            'bgpPeerRemoteAs' => 64512,
            'bgpPeerDescr' => 'Upstream <b>Transit</b>',
            'context_name' => 'vrf-blue',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.routing', $device))
            ->assertOk()
            ->assertSee('vrf-red')
            ->assertSee('vrf-blue')
            ->assertSee('192.0.2.1')
            ->assertSee('64512')
            ->assertSee('Upstream &lt;b&gt;Transit&lt;/b&gt;', false)
            ->assertDontSee('Upstream <b>Transit</b>', false);
    }

    public function testUserCannotViewRoutingPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.routing', $device))
            ->assertForbidden();
    }

    public function testUpdateContextsNormalizesAndStores(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('device.edit.routing.contexts', $device), [
                'snmp_contexts' => [' vrf-red ', 'vrf-blue', 'vrf-red', ''],
            ])
            ->assertRedirect(route('device.edit.routing', $device->device_id));

        $this->assertSame('["vrf-red","vrf-blue"]', $device->fresh()->getAttrib('routing_snmp_contexts'));
    }

    public function testUpdateContextsEmptyForgetsAttribute(): void
    {
        $device = Device::factory()->create();
        $device->setAttrib('routing_snmp_contexts', json_encode(['vrf-red']));

        $this->actingAs($this->admin())
            ->put(route('device.edit.routing.contexts', $device), [])
            ->assertRedirect(route('device.edit.routing', $device->device_id));

        $this->assertNull($device->fresh()->getAttrib('routing_snmp_contexts'));
    }

    public function testUserCannotUpdateContexts(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->put(route('device.edit.routing.contexts', $device), ['snmp_contexts' => ['vrf-red']])
            ->assertForbidden();

        $this->assertNull($device->fresh()->getAttrib('routing_snmp_contexts'));
    }

    public function testAdminCanUpdatePeerDescription(): void
    {
        $device = Device::factory()->create();
        $peer = BgpPeer::factory()->for($device)->create(['bgpPeerDescr' => 'old']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.routing.peer.update', [$device, $peer]), ['descr' => 'new description'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame('new description', $peer->fresh()->bgpPeerDescr);
    }

    public function testClearingPeerDescriptionStoresEmptyString(): void
    {
        $device = Device::factory()->create();
        $peer = BgpPeer::factory()->for($device)->create(['bgpPeerDescr' => 'old']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.routing.peer.update', [$device, $peer]), ['descr' => ''])
            ->assertOk();

        $this->assertSame('', $peer->fresh()->bgpPeerDescr);
    }

    public function testPeerMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherPeer = BgpPeer::factory()->for(Device::factory())->create(['bgpPeerDescr' => 'old']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.routing.peer.update', [$device, $otherPeer]), ['descr' => 'hijack'])
            ->assertNotFound();

        $this->assertSame('old', $otherPeer->fresh()->bgpPeerDescr);
    }

    public function testUserCannotUpdatePeerDescription(): void
    {
        $device = Device::factory()->create();
        $peer = BgpPeer::factory()->for($device)->create(['bgpPeerDescr' => 'old']);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.routing.peer.update', [$device, $peer]), ['descr' => 'new'])
            ->assertForbidden();

        $this->assertSame('old', $peer->fresh()->bgpPeerDescr);
    }
}
