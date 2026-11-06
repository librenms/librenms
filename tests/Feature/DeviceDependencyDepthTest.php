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

    public function testUpdateMaxDepthStandalone(): void
    {
        $device = Device::factory()->create(['max_depth' => 5]);

        $device->updateMaxDepth();

        $this->assertSame(0, $device->fresh()->max_depth);
    }

    public function testUpdateMaxDepthRootWithChildren(): void
    {
        $root = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($root);
        $root->max_depth = 0;
        $root->save();

        $root->updateMaxDepth();

        $this->assertSame(1, $root->fresh()->max_depth);
    }

    public function testUpdateMaxDepthUsesDeepestParent(): void
    {
        $shallow = Device::factory()->create(['max_depth' => 1]);
        $deep = Device::factory()->create(['max_depth' => 4]);
        $child = Device::factory()->create();
        $child->parents()->attach([$shallow->device_id, $deep->device_id]);
        $deep->forceFill(['max_depth' => 4])->save();

        $child->fresh()->updateMaxDepth();

        $this->assertSame(5, $child->fresh()->max_depth);
    }

    public function testUpdateMaxDepthExcludesParent(): void
    {
        $shallow = Device::factory()->create();
        $deep = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach([$shallow->device_id, $deep->device_id]);
        $shallow->forceFill(['max_depth' => 1])->save();
        $deep->forceFill(['max_depth' => 4])->save();

        $child->fresh()->updateMaxDepth($deep->device_id);
        $this->assertSame(2, $child->fresh()->max_depth);

        $child->fresh()->updateMaxDepth($shallow->device_id);
        $this->assertSame(5, $child->fresh()->max_depth);
    }

    public function testUpdateMaxDepthExcludingOnlyParent(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $child->fresh()->updateMaxDepth($parent->device_id);

        $this->assertSame(0, $child->fresh()->max_depth);
    }

    public function testValidateStandaloneMarksRootWithChildren(): void
    {
        $root = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($root);
        $root->forceFill(['max_depth' => 0])->save();

        $root->fresh()->validateStandalone();

        $this->assertSame(1, $root->fresh()->max_depth);
    }

    public function testValidateStandaloneMarksLoneRootStandalone(): void
    {
        $device = Device::factory()->create(['max_depth' => 1]);

        $device->fresh()->validateStandalone();

        $this->assertSame(0, $device->fresh()->max_depth);
    }

    public function testValidateStandaloneKeepsRootWithChildren(): void
    {
        $root = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($root);

        $root->fresh()->validateStandalone();

        $this->assertSame(1, $root->fresh()->max_depth);
    }

    public function testValidateStandaloneIgnoresDeeperDevices(): void
    {
        $root = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($root);

        $child->fresh()->validateStandalone();

        $this->assertSame(2, $child->fresh()->max_depth);
    }

    public function testDeletingParentUpdatesChildDepth(): void
    {
        $root = Device::factory()->create();
        $middle = Device::factory()->create();
        $leaf = Device::factory()->create();
        $middle->parents()->attach($root);
        $leaf->parents()->attach([$root->device_id, $middle->device_id]);
        $this->assertSame(3, $leaf->fresh()->max_depth);

        $middle->fresh()->delete();

        $this->assertSame(2, $leaf->fresh()->max_depth);
    }

    public function testDeletingOnlyParentMakesChildStandalone(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $parent->fresh()->delete();

        $this->assertSame(0, $child->fresh()->max_depth);
    }

    public function testDetachParentUpdatesDepth(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $child->parents()->detach($parent);

        $this->assertSame(0, $parent->fresh()->max_depth);
        $this->assertSame(0, $child->fresh()->max_depth);
    }

    public function testAttachParentUpdatesDescendantDepth(): void
    {
        $root = Device::factory()->create();
        $middle = Device::factory()->create();
        $leaf = Device::factory()->create();
        $leaf->parents()->attach($middle);

        $middle->parents()->attach($root);

        $this->assertSame(2, $middle->fresh()->max_depth);
        $this->assertSame(3, $leaf->fresh()->max_depth);
    }

    public function testDeletingChildMakesParentStandalone(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $child->fresh()->delete();

        $this->assertSame(0, $parent->fresh()->max_depth);
    }

    public function testDetachAllParentsUpdatesDepth(): void
    {
        $first = Device::factory()->create();
        $second = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach([$first->device_id, $second->device_id]);

        $child->parents()->detach($child->parents()->allRelatedIds());

        $this->assertSame(0, $first->fresh()->max_depth);
        $this->assertSame(0, $second->fresh()->max_depth);
        $this->assertSame(0, $child->fresh()->max_depth);
    }

    public function testDetachMiddleUpdatesDescendants(): void
    {
        $root = Device::factory()->create();
        $middle = Device::factory()->create();
        $leaf = Device::factory()->create();
        $middle->parents()->attach($root);
        $leaf->parents()->attach($middle);

        $middle->parents()->detach($root);

        $this->assertSame(0, $root->fresh()->max_depth);
        $this->assertSame(1, $middle->fresh()->max_depth);
        $this->assertSame(2, $leaf->fresh()->max_depth);
    }

    public function testDependencyLoopDoesNotRecurseForever(): void
    {
        $first = Device::factory()->create();
        $second = Device::factory()->create();
        $second->parents()->attach($first);

        $first->parents()->attach($second);

        $this->assertGreaterThan(0, $first->fresh()->max_depth);
        $this->assertGreaterThan(0, $second->fresh()->max_depth);
    }
}
