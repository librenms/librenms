<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class DeviceDependencyControllerTest extends TestCase
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

    public function testAdminCanViewPage(): void
    {
        $this->actingAs($this->admin())
            ->get(route('device-dependencies.index'))
            ->assertOk()
            ->assertSee('x-data="deviceDependencies"', false)
            ->assertSee('id="hostdeps"', false);
    }

    public function testUserCannotViewPage(): void
    {
        $this->actingAs($this->user())
            ->get(route('device-dependencies.index'))
            ->assertForbidden();
    }

    public function testTableListsDevicesWithParents(): void
    {
        $parent = Device::factory()->create(['hostname' => 'dep-parent.example.com']);
        $child = Device::factory()->create(['hostname' => 'dep-child.example.com']);
        $child->parents()->attach($parent);

        $response = $this->actingAs($this->admin())
            ->postJson(route('table.device-dependencies'), ['searchPhrase' => 'dep-', 'rowCount' => -1])
            ->assertOk();

        $rows = $response->json('rows');
        $this->assertIsArray($rows);
        $rows = collect($rows)->keyBy('device_id');
        $this->assertSame(__('None'), $rows[$parent->device_id]['parents']);
        $this->assertSame('[]', $rows[$parent->device_id]['parents_json']);
        $this->assertStringContainsString('dep-parent.example.com', $rows[$child->device_id]['parents']);
        $this->assertSame(
            [['id' => $parent->device_id, 'text' => $parent->display]],
            json_decode($rows[$child->device_id]['parents_json'], true)
        );
    }

    public function testTableSearchMatchesParentHostname(): void
    {
        $parent = Device::factory()->create(['hostname' => 'uniq-parent-search.example.com']);
        $child = Device::factory()->create(['hostname' => 'other-child.example.com']);
        $child->parents()->attach($parent);

        $response = $this->actingAs($this->admin())
            ->postJson(route('table.device-dependencies'), ['searchPhrase' => 'uniq-parent-search'])
            ->assertOk();

        $rows = $response->json('rows');
        $this->assertIsArray($rows);
        $this->assertEqualsCanonicalizing([$parent->device_id, $child->device_id], array_column($rows, 'device_id'));
    }

    public function testUserCannotViewTable(): void
    {
        $this->actingAs($this->user())
            ->postJson(route('table.device-dependencies'))
            ->assertForbidden();
    }

    public function testUpdateSetsParentsForDevices(): void
    {
        [$parent1, $parent2, $old_parent] = Device::factory()->count(3)->create();
        [$child1, $child2] = Device::factory()->count(2)->create();
        $child1->parents()->attach($old_parent);

        $this->actingAs($this->admin())
            ->putJson(route('device-dependencies.update'), [
                'device_ids' => [$child1->device_id, $child2->device_id],
                'parent_ids' => [$parent1->device_id, $parent2->device_id],
            ])
            ->assertOk();

        $expected = [$parent1->device_id, $parent2->device_id];
        $this->assertEqualsCanonicalizing($expected, $child1->parents()->pluck('devices.device_id')->all());
        $this->assertEqualsCanonicalizing($expected, $child2->parents()->pluck('devices.device_id')->all());
    }

    public function testUpdateWithoutParentsClearsDependencies(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $this->actingAs($this->admin())
            ->putJson(route('device-dependencies.update'), ['device_ids' => [$child->device_id]])
            ->assertOk()
            ->assertJson(['message' => 'Device dependencies have been removed']);

        $this->assertSame(0, $child->parents()->count());
    }

    public function testUpdateRejectsSelfDependencyAndUnknownDevices(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->putJson(route('device-dependencies.update'), [
                'device_ids' => [$device->device_id],
                'parent_ids' => [$device->device_id, 999999999],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_ids.0', 'parent_ids.1']);

        $this->actingAs($this->admin())
            ->putJson(route('device-dependencies.update'), ['parent_ids' => [$device->device_id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['device_ids']);

        $this->assertSame(0, $device->parents()->count());
    }

    public function testUserCannotUpdateDependencies(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();

        $this->actingAs($this->user())
            ->putJson(route('device-dependencies.update'), [
                'device_ids' => [$child->device_id],
                'parent_ids' => [$parent->device_id],
            ])
            ->assertForbidden();

        $this->assertSame(0, $child->parents()->count());
    }

    public function testDestroyClearsChildrenOfParents(): void
    {
        [$parent, $other_parent] = Device::factory()->count(2)->create();
        [$child1, $child2, $other_child] = Device::factory()->count(3)->create();
        $child1->parents()->attach($parent);
        $child2->parents()->attach([$parent->device_id, $other_parent->device_id]);
        $other_child->parents()->attach($other_parent);

        $this->actingAs($this->admin())
            ->deleteJson(route('device-dependencies.destroy'), ['parent_ids' => [$parent->device_id]])
            ->assertOk();

        $this->assertSame(0, $parent->children()->count());
        $this->assertEquals([$other_parent->device_id], $child2->parents()->pluck('devices.device_id')->all());
        $this->assertEquals([$other_parent->device_id], $other_child->parents()->pluck('devices.device_id')->all());
    }

    public function testUserCannotDestroyDependencies(): void
    {
        $parent = Device::factory()->create();
        $child = Device::factory()->create();
        $child->parents()->attach($parent);

        $this->actingAs($this->user())
            ->deleteJson(route('device-dependencies.destroy'), ['parent_ids' => [$parent->device_id]])
            ->assertForbidden();

        $this->assertSame(1, $child->parents()->count());
    }
}
