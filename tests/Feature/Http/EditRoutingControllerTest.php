<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\User;
use App\Models\Vrf;
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
        Vrf::factory()->for($device)->create(['vrf_oid' => 'vrf-red', 'vrf_name' => 'vrf-red', 'context_name' => 'vrf-red', 'context_manual' => true, 'vrf_manual' => true]);
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

    public function testRoutingPageShowsContextStatus(): void
    {
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create(['vrf_name' => 'red', 'context_name' => 'vrf-red', 'context_verified' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'blue', 'context_name' => 'vrf-blue', 'context_manual' => true, 'context_verified' => false, 'context_failures' => 1]);
        Vrf::factory()->for($device)->create(['vrf_oid' => 'vrf-pending', 'vrf_name' => 'vrf-pending', 'context_name' => 'vrf-pending', 'context_manual' => true, 'vrf_manual' => true]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'green', 'context_name' => 'vrf-green', 'context_verified' => true, 'context_enabled' => false]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'mgmt', 'context_name' => null]);

        $vrfs = $this->actingAs($this->admin())
            ->get(route('device.edit.routing', $device))
            ->assertOk()
            ->viewData('vrfs');

        $this->assertSame([
            ['blue', 'vrf-blue', false, false, true, 'failing'],
            ['green', 'vrf-green', true, false, false, 'disabled'],
            ['mgmt', null, false, false, true, 'none'],
            ['red', 'vrf-red', true, false, false, 'answering'],
            ['vrf-pending', 'vrf-pending', false, true, false, 'waiting'],
        ], array_map(fn ($vrf) => [$vrf['name'], $vrf['context'], $vrf['device_context'], $vrf['context_only'], $vrf['editable'], $vrf['status']], $vrfs));
    }

    public function testBgpPeersPanelIsOnlyShownWithPeers(): void
    {
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('device.edit.routing', $device))
            ->assertOk()
            ->assertDontSee('BGP Peers');

        BgpPeer::factory()->for($device)->create();

        $this->actingAs($admin)
            ->get(route('device.edit.routing', $device))
            ->assertOk()
            ->assertSee('BGP Peers');
    }

    public function testRoutingTabIsShownForDevicesWithVrfs(): void
    {
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device.edit', $device))
            ->assertOk()
            ->assertSee(route('device.edit.routing', $device->device_id));
    }

    public function testUserCannotViewRoutingPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.routing', $device))
            ->assertForbidden();
    }

    public function testUpdateVrfEnablesAndDisablesContexts(): void
    {
        $device = Device::factory()->create();
        $red = Vrf::factory()->for($device)->create(['context_name' => 'vrf-red', 'context_enabled' => true, 'context_verified' => true]);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $red]), ['context_enabled' => false])
            ->assertOk()
            ->assertJson(['enabled' => false, 'status' => 'disabled']);
        $this->assertFalse($red->fresh()->context_enabled);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $red]), ['context_enabled' => true])
            ->assertOk()
            ->assertJson(['enabled' => true, 'status' => 'answering']);
        $this->assertTrue($red->fresh()->context_enabled);
    }

    public function testVrfMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $other = Vrf::factory()->for(Device::factory())->create(['context_name' => 'vrf-other', 'context_enabled' => true, 'context_manual' => true, 'vrf_manual' => true]);

        $this->actingAs($this->admin())
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $other]), ['context_enabled' => false])
            ->assertNotFound();
        $this->actingAs($this->admin())
            ->deleteJson(route('device.edit.routing.vrf.destroy', [$device, $other]))
            ->assertNotFound();

        $this->assertTrue($other->fresh()->context_enabled);
    }

    public function testUpdateVrfSetsAndClearsContexts(): void
    {
        $device = Device::factory()->create();
        $blue = Vrf::factory()->for($device)->create(['vrf_name' => 'blue', 'context_name' => null]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'green', 'context_name' => 'ctx-green']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $blue]), ['context_name' => ' ctx-blue '])
            ->assertOk()
            ->assertJson(['context' => 'ctx-blue', 'device_context' => false, 'editable' => true, 'status' => 'waiting']);
        $blue->refresh();
        $this->assertSame(['ctx-blue', true, false], [$blue->context_name, $blue->context_manual, $blue->vrf_manual]);

        $this->actingAs($admin)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $blue]), ['context_name' => 'ctx-green'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['context_name' => 'Context ctx-green is used by VRF green.']);
        $this->assertSame('ctx-blue', $blue->fresh()->context_name);

        $this->actingAs($admin)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $blue]), ['context_name' => 'CTX-GREEN'])
            ->assertOk()
            ->assertJson(['context' => 'CTX-GREEN']);

        $this->actingAs($admin)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $blue]), ['context_name' => ''])
            ->assertOk()
            ->assertJson(['context' => null, 'editable' => true, 'status' => 'none']);
        $this->assertNull($blue->fresh()->context_name);
    }

    public function testUpdateVrfOnlyChangesContextsUsersSet(): void
    {
        $device = Device::factory()->create();
        $mapped = Vrf::factory()->for($device)->create(['vrf_name' => 'green', 'context_name' => 'ctx-green']);
        $contextOnly = Vrf::factory()->for($device)->create(['vrf_oid' => 'red', 'vrf_name' => 'red', 'context_name' => 'red', 'context_manual' => true, 'vrf_manual' => true]);
        $default = Vrf::factory()->for($device)->create(['vrf_name' => 'default', 'context_name' => null]);
        $admin = $this->admin();

        foreach ([$mapped, $contextOnly, $default] as $vrf) {
            foreach (['other', null] as $context) {
                $this->actingAs($admin)
                    ->patchJson(route('device.edit.routing.vrf.update', [$device, $vrf]), ['context_name' => $context])
                    ->assertUnprocessable()
                    ->assertJsonValidationErrors('context_name');
            }
        }

        $this->assertSame('ctx-green', $mapped->fresh()->context_name);
        $this->assertSame('red', $contextOnly->fresh()->context_name);
        $this->assertNull($default->fresh()->context_name);
    }

    public function testStoreContextAddsVrfsTheDeviceDoesNotList(): void
    {
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create(['vrf_name' => 'Blue', 'context_name' => null]);
        Vrf::factory()->for($device)->create(['vrf_name' => 'green', 'context_name' => 'ctx-green']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('device.edit.routing.vrf.store', $device), ['context' => ' vrf-new '])
            ->assertOk()
            ->assertJsonCount(3, 'vrfs')
            ->assertJson(['message' => 'VRF vrf-new added']);
        $new = $device->vrfs()->firstWhere('vrf_name', 'vrf-new');
        $this->assertSame(['context:vrf-new', 'vrf-new', true, true], [$new->vrf_oid, $new->context_name, $new->context_manual, $new->vrf_manual]);

        foreach ([
            'blue' => 'VRF Blue exists, set its context in the table.',
            'ctx-green' => 'Context ctx-green is used by VRF green.',
            'vrf-new' => 'Context vrf-new is used by VRF vrf-new.',
            'default' => 'The default VRF is polled without a context.',
        ] as $context => $error) {
            $this->actingAs($admin)
                ->postJson(route('device.edit.routing.vrf.store', $device), ['context' => $context])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['context' => $error]);
        }

        $this->actingAs($admin)
            ->postJson(route('device.edit.routing.vrf.store', $device), ['context' => ' '])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('context');

        $this->assertSame(3, $device->vrfs()->count());
    }

    public function testDestroyVrfRemovesVrfsOnlyKnownThroughTheirContext(): void
    {
        $device = Device::factory()->create();
        $created = Vrf::factory()->for($device)->create(['vrf_oid' => 'vrf-red', 'vrf_name' => 'vrf-red', 'context_name' => 'vrf-red', 'context_manual' => true, 'vrf_manual' => true]);
        $attached = Vrf::factory()->for($device)->create(['vrf_name' => 'blue', 'context_name' => 'ctx-blue', 'context_manual' => true, 'context_verified' => true]);
        $mapped = Vrf::factory()->for($device)->create(['vrf_name' => 'green', 'context_name' => 'ctx-green']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->deleteJson(route('device.edit.routing.vrf.destroy', [$device, $created]))
            ->assertOk()
            ->assertJsonCount(2, 'vrfs');
        $this->assertNull($created->fresh(), 'VRFs only known through their context are deleted');

        foreach ([$attached, $mapped] as $listed) {
            $this->actingAs($admin)
                ->deleteJson(route('device.edit.routing.vrf.destroy', [$device, $listed]))
                ->assertUnprocessable();
        }

        $this->assertSame('ctx-blue', $attached->fresh()->context_name, 'VRFs the device lists are not removed');
        $this->assertSame('ctx-green', $mapped->fresh()->context_name);
    }

    public function testUserCannotUpdateContexts(): void
    {
        $device = Device::factory()->create();
        $vrf = Vrf::factory()->for($device)->create(['context_name' => 'vrf-red', 'context_manual' => true, 'vrf_manual' => true]);
        $user = $this->user();

        $this->actingAs($user)
            ->postJson(route('device.edit.routing.vrf.store', $device), ['context' => 'vrf-blue'])
            ->assertForbidden();
        $this->actingAs($user)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $vrf]), ['context_enabled' => false])
            ->assertForbidden();
        $this->actingAs($user)
            ->patchJson(route('device.edit.routing.vrf.update', [$device, $vrf]), ['context_name' => 'vrf-blue'])
            ->assertForbidden();
        $this->actingAs($user)
            ->deleteJson(route('device.edit.routing.vrf.destroy', [$device, $vrf]))
            ->assertForbidden();

        $this->assertSame(1, $device->vrfs()->count());
        $this->assertTrue($vrf->fresh()->context_enabled);
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
