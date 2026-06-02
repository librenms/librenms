<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Spatie\Permission\Models\Role;

class GraphsPageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LibrenmsConfig::set('webui.dynamic_graphs', false);
    }

    public function testAuthenticatedDeviceGraphPageRenders(): void
    {
        $device = Device::factory()->create(['hostname' => 'graph-device.example.com']);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs/device={$device->device_id}/type=device_poller_perf/");

        $response->assertOk();
        $response->assertSee('Poller Time');
        $response->assertSee('graph.php?device=' . $device->device_id, false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testDateSelectorUsesSelectedRange(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs/device={$device->device_id}/type=device_poller_perf/from=1700000000/to=1700003600/");

        $response->assertOk();
        $response->assertSee('id="customrange"', false);
        $response->assertSee('new Date(1700000000*1000)', false);
        $response->assertSee('new Date(1700003600*1000)', false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    public function testShowCommandUsesGraphVars(): void
    {
        $device = Device::factory()->create(['hostname' => 'port-device.example.com']);
        $port = Port::factory()->for($device)->create([
            'ifName' => 'GigabitEthernet0/1',
            'ifDescr' => 'GigabitEthernet0/1',
            'ifAlias' => 'uplink',
            'ifSpeed' => 1000000000,
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get("/graphs/id={$port->port_id}/type=port_bits/showcommand=yes/");

        $response->assertOk();
        $response->assertSee('RRDTool Command');
        $response->assertSee('port-id' . $port->port_id . '.rrd', false);
        $this->assertNoPhpWarningOutput($response->getContent());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testUnauthenticatedGraphPageRedirectsToLogin(): void
    {
        $device = Device::factory()->create();
        Auth::shouldUse('web');
        Auth::logout();
        Auth::forgetGuards();
        $this->flushSession();

        $this->get("/graphs/device={$device->device_id}/type=device_poller_perf/")
            ->assertRedirect('/login');
    }

    private function adminUser(): User
    {
        return User::factory()->create(['enabled' => 1])
            ->assignRole('admin');
    }

    private function assertNoPhpWarningOutput(string $content): void
    {
        $this->assertStringNotContainsString('Undefined variable', $content);
        $this->assertStringNotContainsString('Warning', $content);
        $this->assertStringNotContainsString('Notice', $content);
    }
}
