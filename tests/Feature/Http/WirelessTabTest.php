<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class WirelessTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testOverviewShowsClassGraphs(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients', 'sensor_descr' => 'Radio <1>']);

        $this->actingAs($this->admin())
            ->get(route('device.wireless', ['device' => $device]))
            ->assertOk()
            ->assertSee('type=device_wireless_clients', false)
            ->assertSee('wireless?metric=clients', false)
            ->assertDontSee('Radio');
    }

    public function testMetricListsSensors(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients', 'sensor_descr' => 'Radio <1>', 'sensor_current' => 7]);
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'snr', 'sensor_descr' => 'Other Sensor']);

        $this->actingAs($this->admin())
            ->get(route('device.wireless', ['device' => $device, 'metric' => 'clients']))
            ->assertOk()
            ->assertSee('Radio &lt;1&gt;', false)
            ->assertDontSee('Other Sensor')
            ->assertSee('type=wireless_clients', false);
    }

    public function testUnknownMetricShowsOverview(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients', 'sensor_descr' => 'Radio <1>']);

        $this->actingAs($this->admin())
            ->get(route('device.wireless', ['device' => $device, 'metric' => 'nope']))
            ->assertOk()
            ->assertViewHas('metric', null)
            ->assertDontSee('Radio');
    }

    public function testLegacyPathRedirectsToQuery(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get("device/$device->device_id/wireless/metric=clients")
            ->assertStatus(301)
            ->assertRedirect(route('device.wireless', ['device' => $device, 'metric' => 'clients']));
    }

    public function testUnauthorizedUserIsForbidden(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->get(route('device.wireless', ['device' => $device]))
            ->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
