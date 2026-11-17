<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\PeeringdbIx;
use App\Models\PeeringdbIxPeer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class PeeringPagesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testIndexListsPublicAsnsOfAccessibleDevices(): void
    {
        Device::factory()->create(['bgpLocalAs' => 64496]);
        Device::factory()->create(['bgpLocalAs' => 64512]); // private
        $hidden = Device::factory()->create(['bgpLocalAs' => 64497]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('peering-db.view'));
        $user->devicesOwned()->attach(Device::where('bgpLocalAs', '!=', $hidden->bgpLocalAs)->pluck('device_id'));

        $this->actingAs($user)
            ->get(route('peering.index'))
            ->assertOk()
            ->assertSee(route('peering.exchanges', 64496))
            ->assertDontSee(route('peering.exchanges', 64512))
            ->assertDontSee(route('peering.exchanges', 64497));
    }

    public function testPagesRequirePermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)->get(route('peering.index'))->assertForbidden();
        $this->actingAs($user)->get(route('peering.exchanges', 64496))->assertForbidden();
    }

    public function testExchangesAndPeersWithConnectedFilter(): void
    {
        PeeringdbIx::create(['ix_id' => 7, 'name' => 'Example <IX>', 'asn' => 64496, 'timestamp' => time()]);
        PeeringdbIxPeer::create(['ix_id' => 7, 'peer_id' => 1, 'remote_asn' => 64500, 'remote_ipaddr4' => '192.0.2.10', 'name' => 'Connected Peer', 'timestamp' => time()]);
        PeeringdbIxPeer::create(['ix_id' => 7, 'peer_id' => 2, 'remote_asn' => 64501, 'remote_ipaddr4' => '192.0.2.11', 'name' => 'Other Peer', 'timestamp' => time()]);
        BgpPeer::factory()->for(Device::factory())->create(['bgpPeerIdentifier' => '192.0.2.10']);

        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('peering.exchanges', 64496))
            ->assertOk()
            ->assertSee('Example &lt;IX&gt;', false)
            ->assertSee(route('peering.peers', [64496, 7]));

        $this->actingAs($admin)
            ->get(route('peering.peers', [64496, 7, 'status' => 'connected']))
            ->assertOk()
            ->assertSee('Connected Peer')
            ->assertDontSee('Other Peer');

        $this->actingAs($admin)
            ->get(route('peering.peers', [64496, 7, 'status' => 'unconnected']))
            ->assertOk()
            ->assertSee('Other Peer')
            ->assertDontSee('Connected Peer');

        $this->actingAs($admin)
            ->get(route('peering.peers', [64496, 7, 'search' => '64501']))
            ->assertOk()
            ->assertSee('Other Peer')
            ->assertDontSee('Connected Peer');
    }
}
