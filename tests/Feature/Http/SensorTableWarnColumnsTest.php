<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SensorTableWarnColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testSensorTableRowsContainTheWarnLimitColumns(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device, [
            'sensor_limit_low' => 10.0,
            'sensor_limit_low_warn' => 20.0,
            'sensor_limit_warn' => 80.0,
            'sensor_limit' => 90.0,
        ]));
        Sensor::forceCreate($this->sensorAttributes($device, [
            'sensor_descr' => 'No Warns',
            'sensor_current' => 40.0,
        ]));

        $rows = $this->actingAs($this->admin())
            ->postJson(route('table.sensors'), ['view' => 'detail', 'class' => 'all'])
            ->assertOk()
            ->json('rows');

        $this->assertCount(2, $rows);

        $with_warns = $rows[0];
        $this->assertStringContainsString('20', $with_warns['sensor_limit_low_warn']);
        $this->assertStringContainsString('80', $with_warns['sensor_limit_warn']);
        $this->assertStringContainsString('10', $with_warns['sensor_limit_low']);
        $this->assertStringContainsString('90', $with_warns['sensor_limit']);

        $without_warns = $rows[1];
        $this->assertSame('<span class="label label-default">-</span>', $without_warns['sensor_limit_low_warn']);
        $this->assertSame('<span class="label label-default">-</span>', $without_warns['sensor_limit_warn']);
    }

    public function testSensorIndexPageShowsTheWarnLimitColumns(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device));

        $html = $this->actingAs($this->admin())
            ->get(route('sensor.index'))
            ->assertOk()
            ->getContent();

        $position = 0;

        foreach (['sensor_limit_low', 'sensor_limit_low_warn', 'sensor_limit_warn', 'sensor_limit'] as $column) {
            $needle = "data-column-id=\"$column\"";
            $found = strpos($html, $needle, $position);
            $this->assertNotFalse($found, "Expected to find \"$needle\" after position $position");
            $position = $found + strlen($needle);
        }
    }

    private function sensorAttributes(Device $device, array $overrides = []): array
    {
        return array_merge([
            'device_id' => $device->device_id,
            'sensor_class' => 'temperature',
            'sensor_type' => 'temperature',
            'sensor_oid' => '.1.3.6.1.4.1.991.20.2.9.1.18.1.0',
            'sensor_descr' => 'CPU Temperature',
            'sensor_current' => 50.0,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_limit_warn' => null,
            'sensor_limit' => null,
        ], $overrides);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
