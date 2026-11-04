<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Port\UpdatePortGroupsAction;
use App\Models\Device;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\DBTestCase;

final class PortGroupTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testDynamicGroupSyncsMatchingPortsOnSave(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'access port']);

        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);

        $this->assertEquals([$uplink->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testDynamicGroupCanMatchOnDeviceColumns(): void
    {
        $ios = Device::factory()->create(['os' => 'ios']);
        $linux = Device::factory()->create(['os' => 'linux']);
        Device::factory()->create(['os' => 'ios']); // matching device without ports contributes nothing
        $ios_port = Port::factory()->create(['device_id' => $ios->device_id]);
        Port::factory()->create(['device_id' => $linux->device_id]);

        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('devices.os', 'equal', 'ios'),
        ]);

        $this->assertEquals([$ios_port->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testChangingRulesResyncsMembership(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        $access = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'access port']);

        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);
        $this->assertEquals([$uplink->port_id], $group->ports()->pluck('ports.port_id')->all());

        $group->rules = $this->rulesFor('ports.ifAlias', 'begins_with', 'access');
        $group->save();

        $this->assertEquals([$access->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testStaticGroupMembershipIsNotRecalculated(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        $group = PortGroup::factory()->create(); // static
        $group->ports()->attach($port->port_id);

        $group->updatePorts();

        $this->assertEquals([$port->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testAttachingExistingMembersIsIgnored(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);

        // polling and the daily refresh may both try to add the same membership
        $matching = $group->getPortIdQuery()?->pluck('ports.port_id') ?? collect();
        $this->assertEquals([$uplink->port_id], $matching->all());
        $this->assertSame(0, $group->attachPorts($matching));
        $this->assertSame(1, $group->ports()->count());
    }

    public function testDeletingGroupRemovesPivotRows(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        $group = PortGroup::factory()->create();
        $group->ports()->attach($port->port_id);

        $group->delete();

        $this->assertSame(0, DB::table('port_group_port')->where('port_group_id', $group->id)->count());
    }

    public function testActionOnlyTouchesThePolledDevicesPorts(): void
    {
        $device_a = Device::factory()->create();
        $device_b = Device::factory()->create();
        $port_a = Port::factory()->create(['device_id' => $device_a->device_id, 'ifAlias' => 'uplink a']);
        $port_b = Port::factory()->create(['device_id' => $device_b->device_id, 'ifAlias' => 'uplink b']);

        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);
        $this->assertEqualsCanonicalizing(
            [$port_a->port_id, $port_b->port_id],
            $group->ports()->pluck('ports.port_id')->all()
        );

        // device A changed since the last sync: one port stopped matching, a new one appeared
        DB::table('ports')->where('port_id', $port_a->port_id)->update(['ifAlias' => 'access']);
        $new_port = Port::factory()->create(['device_id' => $device_a->device_id, 'ifAlias' => 'uplink new']);

        $changes = (new UpdatePortGroupsAction($device_a))->execute();

        $this->assertEquals(['attached' => 1, 'detached' => 1], $changes);
        $this->assertEqualsCanonicalizing(
            [$port_b->port_id, $new_port->port_id],
            $group->ports()->pluck('ports.port_id')->all()
        );
    }

    public function testActionLeavesStaticGroupsAlone(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'manually grouped']);

        $group = PortGroup::factory()->create(); // static
        $group->ports()->attach($port->port_id);

        $changes = (new UpdatePortGroupsAction($device))->execute();

        $this->assertEquals(['attached' => 0, 'detached' => 0], $changes);
        $this->assertEquals([$port->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testActionOnUnsavedDeviceDoesNothing(): void
    {
        $changes = (new UpdatePortGroupsAction(new Device))->execute();

        $this->assertEquals(['attached' => 0, 'detached' => 0], $changes);
    }

    public function testPortUpdateRejectsDynamicGroups(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'access port']);
        $dynamic = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);
        $static = PortGroup::factory()->create();
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        // the port does not match the rules, it must not be attached by submitting the group
        $this->actingAs($admin)
            ->putJson(url('port/' . $port->port_id), ['groups' => [$dynamic->id, $static->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('groups');
        $this->assertSame(0, $port->groups()->count());

        $this->actingAs($admin)
            ->putJson(url('port/' . $port->port_id), ['groups' => [$static->id]])
            ->assertOk();
        $this->assertEquals([$static->id], $port->groups()->pluck('port_groups.id')->all());
    }

    public function testInvalidRulesDoNotCreateGroup(): void
    {
        // MySQL only rejects an invalid regex when it has a row to match against
        $device = Device::factory()->create();
        Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->post(route('port-groups.store'), [
                'name' => 'Broken',
                'type' => 'dynamic',
                'rules' => json_encode($this->rulesFor('ports.ifAlias', 'regex', '(')),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('rules');
        $this->assertStringNotContainsString('SQLSTATE', session('errors')->first('rules'));

        $this->assertFalse(PortGroup::where('name', 'Broken')->exists());
    }

    public function testUnknownPortsAreRejected(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $this->actingAs($admin)
            ->post(route('port-groups.store'), ['name' => 'Unknown Port', 'type' => 'static', 'ports' => [999999999]])
            ->assertSessionHasErrors('ports.0');
        $this->assertFalse(PortGroup::where('name', 'Unknown Port')->exists());

        $group = PortGroup::factory()->create();
        $this->actingAs($admin)
            ->put(route('port-groups.update', $group), ['name' => $group->name, 'type' => 'static', 'ports' => [999999999]])
            ->assertSessionHasErrors('ports.0');
        $this->assertSame(0, $group->ports()->count());
    }

    public function testInvalidRulesAreRolledBackOnUpdate(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);
        $rules = $group->rules;

        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->put(route('port-groups.update', $group), [
                'name' => $group->name,
                'desc' => 'broken rule',
                'type' => 'dynamic',
                'rules' => json_encode($this->rulesFor('ports.ifAlias', 'regex', '(')),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('rules');
        $this->assertStringNotContainsString('SQLSTATE', session('errors')->first('rules'));

        $group->refresh();
        $this->assertEquals($rules, $group->rules);
        $this->assertEquals([$uplink->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testConvertingDynamicToStaticKeepsMembers(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        $access = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'access port']);
        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        // the edit form does not list the members of a dynamic group
        $this->actingAs($admin)
            ->get(route('port-groups.edit', $group))
            ->assertOk()
            ->assertDontSee('value="' . $uplink->port_id . '" selected', false);

        $this->actingAs($admin)
            ->put(route('port-groups.update', $group), [
                'name' => $group->name,
                'desc' => 'now static',
                'type' => 'static',
                'ports' => [$access->port_id],
            ])
            ->assertRedirect(route('port-groups.index'));

        $group->refresh();
        $this->assertSame('static', $group->type);
        $ids = $group->ports()->pluck('ports.port_id')->sort()->values()->all();
        $this->assertEquals(collect([$uplink->port_id, $access->port_id])->sort()->values()->all(), $ids);
    }

    /**
     * @return array{condition: string, rules: array<int, array<string, string>>, valid: bool}
     */
    private function rulesFor(string $field, string $operator, string $value): array
    {
        return [
            'condition' => 'AND',
            'rules' => [
                [
                    'id' => $field,
                    'field' => $field,
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => $operator,
                    'value' => $value,
                ],
            ],
            'valid' => true,
        ];
    }
}
