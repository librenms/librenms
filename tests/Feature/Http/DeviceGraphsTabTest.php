<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Availability;
use App\Models\Customoid;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class DeviceGraphsTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testGraphsAreGroupedBySection(): void
    {
        $device = Device::factory()->create();
        $device->graphs()->createMany([['graph' => 'uptime'], ['graph' => 'netstat_tcp'], ['graph' => 'unknown_graph']]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'graphs', 'group' => 'system']))
            ->assertOk()
            ->assertSee('group=netstats', false)
            ->assertSee('type=device_uptime', false)
            ->assertDontSee('type=device_netstat_tcp', false)
            ->assertDontSee('unknown_graph');
    }

    public function testAvailabilityGroup(): void
    {
        $device = Device::factory()->create();
        $device->graphs()->create(['graph' => 'availability']);
        Availability::factory()->create(['device_id' => $device->device_id, 'duration' => 86400, 'availability_perc' => 99.12345]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'graphs']))
            ->assertOk()
            ->assertSee('99.123%')
            ->assertSee('duration=86400', false);
    }

    public function testCustomOidGroup(): void
    {
        $device = Device::factory()->create();
        $device->graphs()->create(['graph' => 'customoid']);
        Customoid::create(['device_id' => $device->device_id, 'customoid_descr' => 'temp', 'customoid_oid' => '.1.3.6', 'customoid_unit' => 'C']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'graphs', 'group' => 'customoid']))
            ->assertOk()
            ->assertSee('Custom OID: temp')
            ->assertSee('type=customoid_temp', false)
            ->assertSee('unit=C', false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
