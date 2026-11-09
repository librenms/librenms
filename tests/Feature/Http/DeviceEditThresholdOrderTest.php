<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class DeviceEditThresholdOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testSensorEditorListsThresholdsFromLowToHigh(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device));

        $html = $this->actingAs($this->admin())
            ->get(route('device.edit.health', $device))
            ->assertOk()
            ->getContent();

        $this->assertInOrder($html, [
            'data-field="sensor_limit_low"',
            'data-field="sensor_limit_low_warn"',
            'data-field="sensor_limit_warn"',
            'data-field="sensor_limit"',
        ]);
    }

    public function testSensorEditorOutlinesTheExceededWarnThreshold(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device, current: 85.0, warn: 80.0, limit: 90.0));

        $html = $this->actingAs($this->admin())
            ->get(route('device.edit.health', $device))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/data-field="sensor_limit_warn"[^>]*style="outline: 2px solid #ffa420;"/',
            $html,
        );
        $this->assertNoOtherOutline($html, 'sensor_limit_warn');
    }

    public function testSensorEditorOutlinesTheExceededHighLimit(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device, current: 95.0, warn: 80.0, limit: 90.0));

        $html = $this->actingAs($this->admin())
            ->get(route('device.edit.health', $device))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/data-field="sensor_limit"[^>]*style="outline: 2px solid #ff0000;"/',
            $html,
        );
        $this->assertNoOtherOutline($html, 'sensor_limit');
    }

    public function testSensorEditorHasNoOutlineWithinThresholds(): void
    {
        $device = Device::factory()->create();
        Sensor::forceCreate($this->sensorAttributes($device, current: 50.0, warn: 80.0, limit: 90.0));

        $html = $this->actingAs($this->admin())
            ->get(route('device.edit.health', $device))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('outline:', $html);
    }

    public function testWirelessSensorEditorListsThresholdsFromLowToHighAndOutlinesTheExceededLowWarn(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::forceCreate([
            'device_id' => $device->device_id,
            'sensor_class' => 'snr',
            'sensor_type' => 'snr',
            'sensor_oids' => '.1.3.6.1.4.1.9999.1.1.1.1.0',
            'sensor_descr' => 'S/N Ratio',
            'sensor_current' => 15.0,
            'sensor_limit_low' => 10.0,
            'sensor_limit_low_warn' => 20.0,
        ]);

        $html = $this->actingAs($this->admin())
            ->get(route('device.edit.wireless-sensors', $device))
            ->assertOk()
            ->getContent();

        $this->assertInOrder($html, [
            'data-field="sensor_limit_low"',
            'data-field="sensor_limit_low_warn"',
            'data-field="sensor_limit_warn"',
            'data-field="sensor_limit"',
        ]);
        $this->assertMatchesRegularExpression(
            '/data-field="sensor_limit_low_warn"[^>]*style="outline: 2px solid #005bdf;"/',
            $html,
        );
    }

    private function sensorAttributes(Device $device, float $current = 50.0, ?float $warn = null, ?float $limit = null): array
    {
        return [
            'device_id' => $device->device_id,
            'sensor_class' => 'temperature',
            'sensor_type' => 'temperature',
            'sensor_oid' => '.1.3.6.1.4.1.991.20.2.9.1.18.1.0',
            'sensor_descr' => 'CPU Temperature',
            'sensor_current' => $current,
            'sensor_limit_warn' => $warn,
            'sensor_limit' => $limit,
        ];
    }

    private function assertInOrder(string $html, array $needles): void
    {
        $position = 0;

        foreach ($needles as $needle) {
            $found = strpos($html, $needle, $position);
            $this->assertNotFalse($found, "Expected to find \"$needle\" after position $position");
            $position = $found + strlen($needle);
        }
    }

    private function assertNoOtherOutline(string $html, string $exceptField): void
    {
        foreach (['sensor_limit_low', 'sensor_limit_low_warn', 'sensor_limit_warn', 'sensor_limit'] as $field) {
            if ($field === $exceptField) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                "/data-field=\"$field\"[^>]*style=\"outline/",
                $html,
                "Expected no outline on the $field input",
            );
        }
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
