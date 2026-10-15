<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class PortGroupPortsControllerTest extends TestCase
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function port(Device $device, array $attributes = []): Port
    {
        return Port::factory()->for($device)->create(array_merge([
            'ifName' => 'eth' . fake()->unique()->numberBetween(0, 10000),
            'ifDescr' => 'Ethernet',
            'ifAlias' => 'Uplink',
            'ifAdminStatus' => 'up',
            'ifOperStatus' => 'up',
            'deleted' => 0,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return int[]
     */
    private function listedPortIds(PortGroup $group, array $query, ?User $user = null): array
    {
        $response = $this->actingAs($user ?? $this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?' . http_build_query($query))
            ->assertOk();

        $ids = array_column($response->json('data'), 'port_id');
        sort($ids);

        return $ids;
    }

    public function testAdminCanViewPage(): void
    {
        $group = PortGroup::factory()->create(['name' => 'Uplinks']);

        $response = $this->actingAs($this->admin())
            ->get(route('port-groups.ports', $group))
            ->assertOk()
            ->assertSee('Uplinks')
            ->assertSee('x-data="portGroupPorts(JSON.parse(', false)
            ->assertSee('x-on:filter:apply.window="if ($event.detail.name === \'port-group.ports\')', false)
            ->assertSee('href="' . route('port-groups.index') . '"', false)
            ->assertDontSee(route('port-groups.edit', $group), false)
            ->assertDontSee(route('portgroup.graph', $group->id), false);

        // every table component includes the shared script, it must only be output once
        $this->assertSame(1, substr_count($response->getContent(), 'window.LibreNMS.selectableTable ='));

        $this->actingAs($this->admin())
            ->get(route('port-groups.index'))
            ->assertOk()
            ->assertSee(route('port-groups.ports', $group->id), false)
            ->assertSee(route('portgroup.graph', $group->id), false);
    }

    public function testUserCannotManagePorts(): void
    {
        $group = PortGroup::factory()->create();
        $port = $this->port(Device::factory()->create());

        $this->actingAs($this->user())
            ->get(route('port-groups.ports', $group))
            ->assertForbidden();

        $this->actingAs($this->user())
            ->getJson(route('port-groups.ports.list', $group))
            ->assertForbidden();

        $this->actingAs($this->user())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add', 'ids' => [$port->port_id]])
            ->assertForbidden();

        $this->assertSame(0, $group->ports()->count());
    }

    public function testListByMembership(): void
    {
        $group = PortGroup::factory()->create();
        $device = Device::factory()->create();
        $member = $this->port($device);
        $other = $this->port($device);
        $group->ports()->attach($member);

        $this->assertSame([$member->port_id], $this->listedPortIds($group, ['membership' => 'members']));
        $this->actingAs($this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?membership=members')
            ->assertJsonPath('data.0.member', true)
            ->assertJsonMissingPath('links')
            ->assertJsonMissingPath('next_page_url');
        $this->assertNotContains($member->port_id, $this->listedPortIds($group, ['membership' => 'available']));
        $this->assertContains($other->port_id, $this->listedPortIds($group, ['membership' => 'available']));

        $response = $this->actingAs($this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?' . http_build_query([
                'membership' => 'all',
                'filter' => ['device_id' => ['eq' => $device->device_id]],
                'sort' => 'ifName',
            ]))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('members', 1);

        $membership = array_column($response->json('data'), 'member', 'port_id');
        $this->assertTrue($membership[$member->port_id]);
        $this->assertFalse($membership[$other->port_id]);
        $this->assertSame($device->display, $response->json('data.0.device'));
    }

    public function testSortByDeviceDisplayName(): void
    {
        $group = PortGroup::factory()->create();
        $first = $this->port(Device::factory()->create(['hostname' => 'zz.example.com', 'display_template' => 'aa-switch']));
        $second = $this->port(Device::factory()->create(['hostname' => 'aa.example.com', 'display_template' => 'zz-switch']));
        $group->ports()->attach([$first->port_id, $second->port_id]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?membership=members&sort=device')
            ->assertOk();

        $this->assertSame([$first->port_id, $second->port_id], array_column($response->json('data'), 'port_id'));
    }

    public function testListPortWithMissingDevice(): void
    {
        $group = PortGroup::factory()->create();
        $device = Device::factory()->create();
        $port = $this->port($device);
        $group->ports()->attach($port);
        $device->deleteQuietly();

        $this->actingAs($this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?membership=members')
            ->assertOk()
            ->assertJsonPath('data.0.port_id', $port->port_id)
            ->assertJsonPath('data.0.device', null)
            ->assertJsonPath('data.0.device_url', null);
    }

    public function testListFilters(): void
    {
        $group = PortGroup::factory()->create();
        $device = Device::factory()->create();
        $uplink = $this->port($device, ['ifAlias' => 'core uplink']);
        $this->port($device, ['ifAlias' => 'customer']);
        $down = $this->port($device, ['ifAlias' => 'spare', 'ifOperStatus' => 'down']);

        $base = ['membership' => 'available', 'filter' => ['device_id' => ['eq' => $device->device_id]]];
        $this->assertSame([$uplink->port_id], $this->listedPortIds($group, array_merge_recursive($base, ['filter' => ['search' => ['contains' => 'uplink']]])));
        $this->assertSame([$down->port_id], $this->listedPortIds($group, array_merge_recursive($base, ['filter' => ['state' => ['eq' => 'down']]])));

        $this->actingAs($this->admin())
            ->getJson(route('port-groups.ports.list', $group) . '?membership=bogus')
            ->assertUnprocessable();
    }

    public function testAddAndRemoveSelectedPorts(): void
    {
        $group = PortGroup::factory()->create();
        $device = Device::factory()->create();
        $ports = collect([$this->port($device), $this->port($device), $this->port($device)]);

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add', 'ids' => $ports->pluck('port_id')->all()])
            ->assertOk()
            ->assertJsonPath('updated', 3)
            ->assertJsonPath('members', 3)
            ->assertJsonPath('message', '3 ports added');

        // adding again does not duplicate
        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add', 'ids' => [$ports[0]->port_id]])
            ->assertOk()
            ->assertJsonPath('updated', 0)
            ->assertJsonPath('members', 3);

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'remove', 'ids' => [$ports[0]->port_id, $ports[1]->port_id]])
            ->assertOk()
            ->assertJsonPath('updated', 2)
            ->assertJsonPath('members', 1)
            ->assertJsonPath('message', '2 ports removed');

        $this->assertSame([$ports[2]->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testAddAndRemoveAllMatchingPorts(): void
    {
        $group = PortGroup::factory()->create();
        $device = Device::factory()->create();
        $up = [$this->port($device), $this->port($device)];
        $down = $this->port($device, ['ifOperStatus' => 'down']);
        $otherDevice = $this->port(Device::factory()->create());

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), [
                'action' => 'add',
                'all' => true,
                'membership' => 'available',
                'filter' => ['device_id' => ['eq' => $device->device_id]],
            ])
            ->assertOk()
            ->assertJsonPath('updated', 3);

        $members = $group->ports()->pluck('ports.port_id')->sort()->values()->all();
        $this->assertSame(collect([$up[0], $up[1], $down])->pluck('port_id')->sort()->values()->all(), $members);
        $this->assertNotContains($otherDevice->port_id, $members);

        $group->ports()->attach($otherDevice);

        // remove only matching members
        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), [
                'action' => 'remove',
                'all' => true,
                'membership' => 'members',
                'filter' => ['state' => ['eq' => 'up'], 'device_id' => ['eq' => $device->device_id]],
            ])
            ->assertOk()
            ->assertJsonPath('updated', 2)
            ->assertJsonPath('members', 2);

        $this->assertEqualsCanonicalizing([$down->port_id, $otherDevice->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testInvalidUpdatesAreRejected(): void
    {
        $group = PortGroup::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add'])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'delete', 'ids' => [1]])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add', 'all' => false])
            ->assertUnprocessable();
    }

    public function testLimitedUserOnlyManagesAccessiblePorts(): void
    {
        Permission::findOrCreate('port-group.update');
        $user = $this->user();
        $user->givePermissionTo('port-group.update');
        $accessible = Device::factory()->create();
        $user->devicesOwned()->attach($accessible->device_id);

        $group = PortGroup::factory()->create();
        $mine = $this->port($accessible);
        $hidden = $this->port(Device::factory()->create());

        $this->assertNotContains($hidden->port_id, $this->listedPortIds($group, ['membership' => 'available'], $user));

        $this->actingAs($user)
            ->postJson(route('port-groups.ports.update', $group), ['action' => 'add', 'ids' => [$mine->port_id, $hidden->port_id]])
            ->assertOk()
            ->assertJsonPath('updated', 1);

        $this->assertSame([$mine->port_id], $group->ports()->pluck('ports.port_id')->all());

        // the member count only includes ports the user can access, matching the members list
        $group->ports()->attach($hidden);
        $this->actingAs($user)
            ->getJson(route('port-groups.ports.list', $group) . '?membership=members')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('members', 1);
    }
}
