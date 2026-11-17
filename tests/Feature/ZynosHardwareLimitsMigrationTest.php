<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Sensor;
use App\Observers\SensorObserver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\DBTestCase;

final class ZynosHardwareLimitsMigrationTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testAutomaticHardwareLimitsMatchFreshDiscoveryAfterUpgrade(): void
    {
        $device = Device::factory()->create();
        $cases = [
            'fanspeed' => ['table' => 1, 'current' => 6700, 'maximum' => 6642, 'minimum' => 6601, 'threshold' => 500, 'fault' => 499],
            'temperature' => ['table' => 2, 'current' => 47, 'maximum' => 46, 'minimum' => 26, 'threshold' => 85, 'fault' => 86],
            'voltage' => ['table' => 3, 'current' => 12.3, 'maximum' => 15.986, 'minimum' => 12.218, 'threshold' => 10.8, 'fault' => 10.7],
        ];

        foreach ($cases as $class => $case) {
            $sensor = Sensor::factory()->create([
                'device_id' => $device->device_id,
                'sensor_type' => 'zynos',
                'sensor_class' => $class,
                'sensor_oid' => '.1.3.6.1.4.1.890.1.15.3.26.1.' . $case['table'] . '.1.3.1',
                'sensor_current' => $case['current'],
                'sensor_custom' => 'No',
                'sensor_limit' => $case['maximum'],
                'sensor_limit_low' => $case['minimum'],
                'sensor_limit_warn' => $class === 'temperature' ? $case['threshold'] : null,
                'sensor_limit_low_warn' => $class === 'temperature' ? null : $case['threshold'],
            ]);
            $limits = $sensor->refresh()->only(Sensor::LIMITS);

            $this->migrate();
            $sensor->refresh();
            $this->assertSame('Reset', $sensor->sensor_custom);
            $this->assertSame($limits, $sensor->only(Sensor::LIMITS));

            // Polling updates values directly and must retain protection before discovery.
            Sensor::query()
                ->where('sensor_class', $class)
                ->where('sensor_id', $sensor->sensor_id)
                ->update(['sensor_current' => $case['fault'], 'sensor_prev' => $case['current']]);
            $sensor->refresh();
            $this->assertSame('Reset', $sensor->sensor_custom);
            $this->assertSame($limits, $sensor->only(Sensor::LIMITS));
            $this->assertSame(Severity::Error, $sensor->currentStatus());

            $discovered = [
                'sensor_current' => $case['current'],
                'sensor_limit' => $class === 'temperature' ? $case['threshold'] : null,
                'sensor_limit_low' => $class === 'temperature' ? null : $case['threshold'],
                'sensor_limit_warn' => null,
                'sensor_limit_low_warn' => null,
            ];
            $sensor->fill($discovered)->save();
            $sensor->refresh();
            $fresh = Sensor::factory()->make(array_merge($discovered, [
                'sensor_class' => $class,
                'sensor_current' => $case['current'],
            ]));
            new SensorObserver(app())->creating($fresh);

            $this->assertSame('No', $sensor->sensor_custom);
            foreach (Sensor::LIMITS as $limit) {
                $this->assertEquals($fresh->$limit, $sensor->$limit, "$class $limit");
            }
            $this->assertSame(Severity::Ok, $sensor->currentStatus());

        }
    }

    public function testCustomLimitsAndUnrelatedSensorsAreUntouched(): void
    {
        $device = Device::factory()->create();
        foreach ([
            ['sensor_custom' => 'Yes'],
            ['sensor_custom' => 'Reset'],
            ['sensor_type' => 'other'],
            ['sensor_oid' => '.1.3.6.1.4.1.890.1.5.8.47.8.1.1.2.1'],
            ['sensor_oid' => '.1.3.6.1.4.1.890.1.15.3.26.1.4.1.3.1'],
            ['sensor_class' => 'temperature'],
            ['sensor_class' => 'humidity'],
        ] as $control) {
            $sensor = Sensor::factory()->create(array_merge([
                'device_id' => $device->device_id,
                'sensor_type' => 'zynos',
                'sensor_class' => 'fanspeed',
                'sensor_oid' => '.1.3.6.1.4.1.890.1.15.3.26.1.1.1.3.1',
                'sensor_custom' => 'No',
                'sensor_limit' => 6642,
                'sensor_limit_low' => 6601,
            ], $control));
            $before = $sensor->refresh()->getAttributes();
            $this->migrate();
            $this->assertSame($before, $sensor->refresh()->getAttributes());
        }
    }

    private function migrate(): void
    {
        (require __DIR__ . '/../../database/migrations/2026_11_17_120000_reset_zynos_hardware_sensor_limits.php')->up();
    }
}
