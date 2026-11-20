<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
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

        $visibility = [
            'sensor_limit_low' => false,
            'sensor_limit_low_warn' => true,
            'sensor_limit_warn' => true,
            'sensor_limit' => false,
        ];

        foreach ($visibility as $column => $hidden) {
            $tag = $this->columnTag($html, $column);

            if ($hidden) {
                $this->assertStringContainsString('data-visible="false"', $tag, "Expected \"$column\" to be hidden by default");
            } else {
                $this->assertStringNotContainsString('data-visible', $tag, "Expected \"$column\" to be visible by default");
            }
        }

        WirelessSensor::forceCreate([
            'device_id' => $device->device_id,
            'sensor_class' => 'quality',
            'sensor_type' => 'signal',
            'sensor_oids' => '[{"var":"signal","type":"gauge","oid":".1.3.6.1.4.1.991.1.1.1.1.0"}]',
        ]);

        $wireless_html = $this->actingAs($this->admin())
            ->get(route('wireless.index', ['metric' => 'quality']))
            ->assertOk()
            ->getContent();

        foreach (['sensor_limit_low_warn', 'sensor_limit_warn'] as $column) {
            $this->assertStringContainsString('data-visible="false"', $this->columnTag($wireless_html, $column), "Expected \"$column\" to be hidden by default on the wireless sensor table");
        }
    }

    public function testSensorIndexPageShowsTheWarnLimitColumnsWhenEnabled(): void
    {
        LibrenmsConfig::set('sensors.warn_limits.enabled', true);

        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device));

        $html = $this->actingAs($this->admin())
            ->get(route('sensor.index'))
            ->assertOk()
            ->getContent();

        foreach (['sensor_limit_low_warn', 'sensor_limit_warn'] as $column) {
            $this->assertStringNotContainsString('data-visible', $this->columnTag($html, $column), "Expected \"$column\" to be visible when sensors.warn_limits.enabled is set");
        }
    }

    private function columnTag(string $html, string $column): string
    {
        preg_match('/<th[^>]*data-column-id="' . $column . '"[^>]*>/', $html, $matches);
        $this->assertNotEmpty($matches, "Expected to find the $column column header");

        return $matches[0];
    }

    /**
     * @param  array<string, int|float|string|null>  $overrides
     * @return array<string, int|float|string|null>
     */
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
