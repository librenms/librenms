<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class HealthTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testOverviewListsMetricGraphs(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);
        Sensor::factory()->create(['device_id' => $device->device_id, 'sensor_class' => 'temperature']);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device]))
            ->assertOk()
            ->assertSee('type=device_processor', false)
            ->assertSee('type=device_temperature', false)
            ->assertSee('health?metric=temperature', false)
            ->assertSee(route('device.edit.health', $device->device_id), false);
    }

    public function testSensorMetricShowsValuesAndLimits(): void
    {
        $device = Device::factory()->create();
        Sensor::factory()->create([
            'device_id' => $device->device_id,
            'sensor_class' => 'temperature',
            'sensor_descr' => 'Inlet <Temp>',
            'sensor_current' => 21,
            'sensor_limit' => 40,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => 'temperature']))
            ->assertOk()
            ->assertSee('Inlet &lt;Temp&gt;', false)
            ->assertSee('Current')
            ->assertSee('High Limit')
            ->assertSee('type=sensor_temperature', false);
    }

    public function testCounterSensorShowsRateButConfiguredLimits(): void
    {
        $device = Device::factory()->create();
        Sensor::factory()->create([
            'device_id' => $device->device_id,
            'sensor_class' => 'count',
            'rrd_type' => 'COUNTER',
            'sensor_current' => 1000,
            'sensor_prev' => 400,
            'sensor_limit' => 5000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => 'count']))
            ->assertOk()
            ->assertSee('Current: 2', false)
            ->assertSee('High Limit: 5000', false);
    }

    public function testMempoolShowsCompositeGraphForMultiplePools(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->count(2)->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => 'mempool']))
            ->assertOk()
            ->assertSee('type=device_mempool', false)
            ->assertSee('type=mempool_usage', false);
    }

    public function testMetricLinksToItsEditPage(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => 'processor']))
            ->assertOk()
            ->assertSee(route('device.edit.processors', $device->device_id), false);
    }

    public function testUnknownMetricFallsBackToOverview(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => '../../etc']))
            ->assertOk()
            ->assertSee('type=device_processor', false);
    }

    public function testEmptyMetricShowsOverview(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device]) . '?metric=')
            ->assertOk()
            ->assertSee('type=device_processor', false)
            ->assertDontSee('type=processor_usage', false);
    }

    public function testNonStringMetricIsRejected(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.health', ['device' => $device, 'metric' => ['processor']]))
            ->assertSessionHasErrors('metric');
    }

    public function testLegacyPathMetricRedirectsToQuery(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get("/device/$device->device_id/health/metric=mempool/")
            ->assertRedirect(route('device.health', ['device' => $device, 'metric' => 'mempool']))
            ->assertStatus(301);
    }

    public function testLegacyDeviceSegmentRedirects(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get("/device/device=$device->device_id/tab=health/metric=processor/")
            ->assertRedirect(route('device.health', ['device' => $device, 'metric' => 'processor']))
            ->assertStatus(301);
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device.health', ['device' => $device]))
            ->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
