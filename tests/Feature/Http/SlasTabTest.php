<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Sla;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SlasTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testListFiltersByTypeAndStatus(): void
    {
        $device = Device::factory()->create();
        Sla::factory()->for($device)->create(['sla_nr' => 11, 'rtt_type' => 'jitter', 'tag' => 'Jitter <Tag>', 'opstatus' => 0]);
        Sla::factory()->for($device)->create(['sla_nr' => 12, 'rtt_type' => 'echo', 'tag' => 'Echo Tag', 'opstatus' => 2]);

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device]))
            ->assertOk()
            ->assertSee('Jitter &lt;Tag&gt;', false)
            ->assertSee('Echo Tag')
            ->assertSee('type=device_sla', false)
            ->assertSee('slas?view=jitter', false)
            ->assertSee('slas?opstatus=down', false);

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device, 'view' => 'jitter']))
            ->assertOk()
            ->assertSee('Jitter &lt;Tag&gt;', false)
            ->assertDontSee('Echo Tag');

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device, 'opstatus' => 'down']))
            ->assertOk()
            ->assertSee('Echo Tag')
            ->assertDontSee('Jitter &lt;Tag&gt;', false);
    }

    public function testDetailShowsTypeGraphs(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create(['rtt_type' => 'jitter']);

        $this->actingAs($this->admin())
            ->get(route('device.slas.show', ['device' => $device, 'sla' => $sla]))
            ->assertOk()
            ->assertSee('Round Trip Time')
            ->assertSee('Mean Opinion Score')
            ->assertSee('type=device_sla_jitter-mos', false);
    }

    public function testDetailLinkUsesSlaRoute(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create(['rtt_type' => 'jitter']);

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device]))
            ->assertOk()
            ->assertSee(route('device.slas.show', ['device' => $device, 'sla' => $sla]), false);
    }

    public function testEmptyAndInvalidOptions(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device]) . '?view=&opstatus=')
            ->assertOk()
            ->assertViewHas('type', 'all')
            ->assertViewHas('status', 'all');

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device, 'opstatus' => 'sideways']))
            ->assertSessionHasErrors('opstatus');
    }

    public function testSlaOfAnotherDeviceIsNotFound(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for(Device::factory())->create();

        $this->actingAs($this->admin())
            ->get(route('device.slas.show', ['device' => $device, 'sla' => $sla]))
            ->assertNotFound();
    }

    public function testDeletedSlaIsNotFound(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create(['deleted' => 1]);

        $this->actingAs($this->admin())
            ->get(route('device.slas.show', ['device' => $device, 'sla' => $sla]))
            ->assertNotFound();
    }

    public function testLegacyPathsRedirect(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create(['rtt_type' => 'jitter']);

        $this->actingAs($this->admin())
            ->get("device/$device->device_id/slas/view=jitter/opstatus=up")
            ->assertStatus(301)
            ->assertRedirect(route('device.slas', ['device' => $device, 'view' => 'jitter', 'opstatus' => 'up']));

        $this->actingAs($this->admin())
            ->get("device/$device->device_id/slas/id=$sla->sla_id")
            ->assertStatus(301)
            ->assertRedirect(route('device.slas', ['device' => $device, 'id' => $sla->sla_id]));

        $this->actingAs($this->admin())
            ->get(route('device.slas', ['device' => $device, 'id' => $sla->sla_id]))
            ->assertStatus(301)
            ->assertRedirect(route('device.slas.show', ['device' => $device, 'sla' => $sla]));
    }

    public function testUnauthorizedUserIsForbidden(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->get(route('device.slas', ['device' => $device]))->assertForbidden();
        $this->actingAs($user)->get(route('device.slas.show', ['device' => $device, 'sla' => $sla]))->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
