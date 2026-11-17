<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class NetflowTabTest extends TestCase
{
    use DatabaseTransactions;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');

        $this->dir = sys_get_temp_dir() . '/librenms-nfsen-' . getmypid();
        File::ensureDirectoryExists($this->dir . '/router_example_com');
        touch($this->dir . '/router_example_com.rrd');
        touch($this->dir . '/router_example_com/upstream.rrd');
        file_put_contents($this->dir . '/router_example_com/upstream-filter.txt', 'port <80>');

        LibrenmsConfig::set('nfsen_enable', true);
        LibrenmsConfig::set('nfsen_rrds', [$this->dir]);
        LibrenmsConfig::set('nfsen_split_char', '_');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function testGeneralGraphsAndChannels(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device.netflow', ['device' => $device]))
            ->assertOk()
            ->assertSee('type=device_nfsen_flows', false)
            ->assertSee('nfsen=channel&amp;channel=upstream', false)
            ->assertDontSee('type=device_nfsen_channel_flows', false);
    }

    public function testChannelShowsFilterAndGraphs(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device.netflow', ['device' => $device, 'nfsen' => 'channel', 'channel' => 'upstream']))
            ->assertOk()
            ->assertSee('port &lt;80&gt;', false)
            ->assertSee('type=device_nfsen_channel_traffic', false)
            ->assertSee('channel=upstream', false);
    }

    public function testUnknownChannelFallsBackToGeneral(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device.netflow', ['device' => $device, 'nfsen' => 'channel', 'channel' => '../router_example_com']))
            ->assertOk()
            ->assertSee('type=device_nfsen_flows', false)
            ->assertDontSee('type=device_nfsen_channel_flows', false);
    }

    public function testStatsFormKeepsValidSelections(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device.netflow', ['device' => $device, 'nfsen' => 'stats', 'topN' => 50, 'lastN' => 3600, 'stattype' => 'dstport', 'statorder' => 'bogus']))
            ->assertOk()
            ->assertSee('<option value="50" selected', false)
            ->assertSee('<option value="3600" selected', false)
            ->assertSee('<option value="dstport" selected', false)
            ->assertSee('<option value="packets" selected', false)
            ->assertDontSee('<pre>', false);
    }

    public function testStatsFormKeepsViewInQuery(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device.netflow', ['device' => $device, 'nfsen' => 'stats']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="nfsen" value="stats">', false)
            ->assertSee('netflow?nfsen=stats', false);
    }

    public function testLegacyPathOptionsRedirectToQuery(): void
    {
        $device = Device::factory()->create(['hostname' => 'router.example.com']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'netflow', 'vars' => 'nfsen=channel/channel=upstream', 'topN' => 20]))
            ->assertRedirect(route('device.netflow', ['device' => $device, 'topN' => 20, 'nfsen' => 'channel', 'channel' => 'upstream']));
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
