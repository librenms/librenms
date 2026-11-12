<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Js;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class DeviceGroupControllerTest extends TestCase
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

    public function testIndexRendersAlpineActions(): void
    {
        $group = DeviceGroup::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device-groups.index'))
            ->assertOk()
            ->assertSee('rediscover(', false)
            ->assertSee('destroy($el', false)
            ->assertSee(Js::from(route('device-groups.rediscover', $group->id))->toHtml(), false)
            ->assertDontSee('ajax_form.php', false);
    }

    public function testAdminCanRediscoverGroup(): void
    {
        $group = DeviceGroup::factory()->create(['name' => 'Core <b>']);
        $members = Device::factory()->count(2)->create(['last_discovered' => '2026-01-01 00:00:00']);
        $other = Device::factory()->create(['last_discovered' => '2026-01-01 00:00:00']);
        $group->devices()->attach($members->pluck('device_id'));

        $this->actingAs($this->admin())
            ->postJson(route('device-groups.rediscover', $group))
            ->assertOk()
            ->assertJson(['message' => 'Devices of group Core &lt;b&gt; will be rediscovered']);

        foreach ($members as $member) {
            $this->assertNull($member->fresh()->last_discovered);
        }
        $this->assertNotNull($other->fresh()->last_discovered);
    }

    public function testUserCannotRediscoverGroup(): void
    {
        $group = DeviceGroup::factory()->create();
        $device = Device::factory()->create(['last_discovered' => '2026-01-01 00:00:00']);
        $group->devices()->attach($device);

        $this->actingAs($this->user())
            ->postJson(route('device-groups.rediscover', $group))
            ->assertForbidden();

        $this->assertNotNull($device->fresh()->last_discovered);
    }

    public function testAdminCanDeleteGroup(): void
    {
        $group = DeviceGroup::factory()->create(['name' => 'Edge']);

        $this->actingAs($this->admin())
            ->deleteJson(route('device-groups.destroy', $group))
            ->assertOk()
            ->assertJson(['message' => 'Device Group Edge deleted']);

        $this->assertNull(DeviceGroup::find($group->id));
    }

    public function testDeleteIsRejectedWhileServiceTemplatesUseGroup(): void
    {
        $group = DeviceGroup::factory()->create();
        $template = ServiceTemplate::factory()->create();
        $group->serviceTemplates()->attach($template);

        $this->actingAs($this->admin())
            ->deleteJson(route('device-groups.destroy', $group))
            ->assertUnprocessable()
            ->assertJsonStructure(['message']);

        $this->assertNotNull(DeviceGroup::find($group->id));
    }

    public function testUserCannotDeleteGroup(): void
    {
        $group = DeviceGroup::factory()->create();

        $this->actingAs($this->user())
            ->deleteJson(route('device-groups.destroy', $group))
            ->assertForbidden();

        $this->assertNotNull(DeviceGroup::find($group->id));
    }
}
