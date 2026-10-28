<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\DeviceGroup;
use LibreNMS\Tests\InMemoryDbTestCase;

final class DeviceGroupScopeTest extends InMemoryDbTestCase
{
    public function testDeviceGroupScopesAcceptCollections(): void
    {
        $group = DeviceGroup::factory()->create();
        $inGroup = Device::factory()->create();
        $free = Device::factory()->create();
        $group->devices()->attach($inGroup);

        // empty collection: a service template with no device groups (#21039).
        // This used to throw "Nested arrays may not be passed to whereIn method"
        $this->assertEqualsCanonicalizing(
            [$inGroup->device_id, $free->device_id],
            Device::notInDeviceGroup(collect([]))->pluck('device_id')->all()
        );
        $this->assertSame(
            [],
            Device::inDeviceGroup(collect([]))->pluck('device_id')->all()
        );

        // non-empty collections keep working, values are the group ids
        $this->assertSame(
            [$free->device_id],
            Device::notInDeviceGroup(collect([$group->id]))->pluck('device_id')->all()
        );
        $this->assertSame(
            [$inGroup->device_id],
            Device::inDeviceGroup(collect([$group->id]))->pluck('device_id')->all()
        );
    }
}
