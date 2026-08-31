<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Port\UpdatePortGroupsAction;
use App\Models\Device;
use App\Models\Port;
use App\Models\PortGroup;
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
