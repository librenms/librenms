<?php

namespace LibreNMS\Tests\Unit\Models;

use App\Models\Sensor;
use App\Models\SensorModel;
use App\Models\WirelessSensor;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Checks which threshold a sensor value currently exceeds, no database needed.
 */
final class SensorThresholdTest extends TestCase
{
    /**
     * @return array<string, array{class-string<SensorModel>}>
     */
    public static function models(): array
    {
        return [
            'sensor' => [Sensor::class],
            'wireless sensor' => [WirelessSensor::class],
        ];
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedingTheHighLimitReportsTheHighLimit(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 100.0,
            'sensor_limit' => 90.0,
            'sensor_limit_warn' => 80.0,
        ]);

        $this->assertSame('sensor_limit', $sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedingTheLowLimitReportsTheLowLimit(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 10.0,
            'sensor_limit_low' => 20.0,
            'sensor_limit_low_warn' => 30.0,
        ]);

        $this->assertSame('sensor_limit_low', $sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedingOnlyTheHighWarnThresholdReportsTheHighWarnThreshold(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 85.0,
            'sensor_limit' => 90.0,
            'sensor_limit_warn' => 80.0,
        ]);

        $this->assertSame('sensor_limit_warn', $sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedingOnlyTheLowWarnThresholdReportsTheLowWarnThreshold(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 25.0,
            'sensor_limit_low' => 20.0,
            'sensor_limit_low_warn' => 30.0,
        ]);

        $this->assertSame('sensor_limit_low_warn', $sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testValueWithinAllThresholdsReportsNothing(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 50.0,
            'sensor_limit' => 90.0,
            'sensor_limit_warn' => 80.0,
            'sensor_limit_low' => 10.0,
            'sensor_limit_low_warn' => 20.0,
        ]);

        $this->assertNull($sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testValueWithoutAnyThresholdsReportsNothing(string $model): void
    {
        $sensor = $this->make($model, ['sensor_current' => 50.0]);

        $this->assertNull($sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testMissingCurrentValueReportsNothing(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_limit' => 90.0,
            'sensor_limit_low' => 10.0,
        ]);

        $this->assertNull($sensor->exceededThreshold());
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedsReportsOnlyTheExceededLimitColumn(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 85.0,
            'sensor_limit' => 90.0,
            'sensor_limit_warn' => 80.0,
            'sensor_limit_low' => 10.0,
            'sensor_limit_low_warn' => 20.0,
        ]);

        $this->assertTrue($sensor->exceeds('sensor_limit_warn'));
        $this->assertFalse($sensor->exceeds('sensor_limit'));
        $this->assertFalse($sensor->exceeds('sensor_limit_low'));
        $this->assertFalse($sensor->exceeds('sensor_limit_low_warn'));
    }

    /**
     * @param  class-string<SensorModel>  $model
     */
    #[DataProvider('models')]
    public function testExceedsReportsNothingWithoutAnyExceededThreshold(string $model): void
    {
        $sensor = $this->make($model, [
            'sensor_current' => 50.0,
            'sensor_limit' => 90.0,
            'sensor_limit_low' => 10.0,
        ]);

        foreach (SensorModel::LIMITS as $limit) {
            $this->assertFalse($sensor->exceeds($limit));
        }
    }

    public function testLimitColorsCoverEveryLimitColumn(): void
    {
        $this->assertEqualsCanonicalizing(SensorModel::LIMITS, array_keys(SensorModel::LIMIT_COLORS));

        foreach (SensorModel::LIMIT_COLORS as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $color);
        }
    }

    /**
     * @param  class-string<SensorModel>  $model
     * @param  array<string, float>  $attributes
     */
    private function make(string $model, array $attributes): SensorModel
    {
        $sensor = new $model;
        $sensor->forceFill($attributes);

        return $sensor;
    }
}
