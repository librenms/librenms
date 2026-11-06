<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use LibreNMS\Tests\InMemoryDbTestCase;

final class DeviceDependencyDepthTest extends InMemoryDbTestCase
{
    public function testAttachParentUpdatesDepth(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();

        $child->parents()->attach($parent);

        $this->assertSame(1, $parent->fresh()->max_depth);
        $this->assertSame(2, $child->fresh()->max_depth);
    }

    public function testAttachChildUpdatesDepth(): void
    {
        $parent = Device::factory()->create()->fresh();
        $child = Device::factory()->create();

        $parent->children()->attach($child);

        $this->assertSame(1, $parent->fresh()->max_depth);
        $this->assertSame(2, $child->fresh()->max_depth);
    }

    public function testSyncMultipleParentsUpdatesDepth(): void
    {
        $first = Device::factory()->create();
        $second = Device::factory()->create();
        $child = Device::factory()->create();

        $child->parents()->sync([$first->device_id, $second->device_id]);

        $this->assertSame(1, $first->fresh()->max_depth);
        $this->assertSame(1, $second->fresh()->max_depth);
        $this->assertSame(2, $child->fresh()->max_depth);
        $this->assertEqualsCanonicalizing(
            [$first->device_id, $second->device_id],
            $child->parents()->pluck('devices.device_id')->all()
        );
    }

    public function testAttachUnderExistingChildUpdatesDepth(): void
    {
        $root = Device::factory()->create();
        $middle = Device::factory()->create();
        $leaf = Device::factory()->create();

        $middle->parents()->attach($root);
        $leaf->parents()->attach($middle);

        $this->assertSame(2, $middle->fresh()->max_depth);
        $this->assertSame(3, $leaf->fresh()->max_depth);
    }

    public function testAttachSecondChildKeepsRootDepth(): void
    {
        $root = Device::factory()->create();
        $first = Device::factory()->create();
        $second = Device::factory()->create();

        $first->parents()->attach($root);
        $second->parents()->attach($root);

        $this->assertSame(1, $root->fresh()->max_depth);
        $this->assertSame(2, $first->fresh()->max_depth);
        $this->assertSame(2, $second->fresh()->max_depth);
    }

    public function testStandaloneDeviceKeepsZeroDepth(): void
    {
        $device = Device::factory()->create();

        $this->assertSame(0, $device->fresh()->max_depth);
    }
}
