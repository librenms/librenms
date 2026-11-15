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
    }

    public function testOverviewListsMetricGraphs(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);
        Sensor::factory()->create(['device_id' => $device->device_id, 'sensor_class' => 'temperature']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'health']))
            ->assertOk()
            ->assertSee('type=device_processor', false)
            ->assertSee('type=device_temperature', false)
            ->assertSee(route('device', ['device' => $device->device_id, 'tab' => 'health', 'metric' => 'temperature']), false);
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
            ->get(route('device', ['device' => $device, 'tab' => 'health', 'metric' => 'temperature']))
            ->assertOk()
            ->assertSee('Inlet &lt;Temp&gt;', false)
            ->assertSee('Current')
            ->assertSee('High Limit')
            ->assertSee('type=sensor_temperature', false);
    }

    public function testMempoolShowsCompositeGraphForMultiplePools(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->count(2)->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'health', 'vars' => 'metric=mempool']))
            ->assertOk()
            ->assertSee('type=device_mempool', false)
            ->assertSee('type=mempool_usage', false);
    }

    public function testUnknownMetricFallsBackToOverview(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create(['device_id' => $device->device_id]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'health', 'metric' => '../../etc']))
            ->assertOk()
            ->assertSee('type=device_processor', false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
