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
            ->get(route('device', ['device' => $device, 'tab' => 'slas']))
            ->assertOk()
            ->assertSee('Jitter &lt;Tag&gt;', false)
            ->assertSee('Echo Tag')
            ->assertSee('type=device_sla', false);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'slas', 'view' => 'jitter']))
            ->assertOk()
            ->assertSee('Jitter &lt;Tag&gt;', false)
            ->assertDontSee('Echo Tag');

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'slas', 'opstatus' => 'down']))
            ->assertOk()
            ->assertSee('Echo Tag')
            ->assertDontSee('Jitter &lt;Tag&gt;', false);
    }

    public function testDetailShowsTypeGraphs(): void
    {
        $device = Device::factory()->create();
        $sla = Sla::factory()->for($device)->create(['rtt_type' => 'jitter']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'slas', 'id' => $sla->sla_id]))
            ->assertOk()
            ->assertSee('Round Trip Time')
            ->assertSee('Mean Opinion Score')
            ->assertSee('type=device_sla_jitter-mos', false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
