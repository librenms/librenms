<?php

namespace LibreNMS\Tests\Unit\Models\Traits;

use App\Models\Traits\HasThresholds;
use PHPUnit\Framework\TestCase;

class HasThresholdsTest extends TestCase
{
    public function test_temperature_guess_only_sets_high_limit(): void
    {
        $sensor = new ThresholdTestSensor('temperature', 50.0);

        $sensor->guessLimits(true, true);

        $this->assertSame(70.0, $sensor->sensor_limit);
        $this->assertNull($sensor->sensor_limit_low);
    }

    public function test_explicit_temperature_low_limit_is_not_changed(): void
    {
        $sensor = new ThresholdTestSensor('temperature', 50.0);
        $sensor->sensor_limit_low = 5.0;

        $sensor->guessLimits(true, false);

        $this->assertSame(70.0, $sensor->sensor_limit);
        $this->assertSame(5.0, $sensor->sensor_limit_low);
    }

    public function test_non_temperature_low_limit_guess_is_unchanged(): void
    {
        $sensor = new ThresholdTestSensor('voltage', 10.0);

        $sensor->guessLimits(true, true);

        $this->assertSame(11.5, $sensor->sensor_limit);
        $this->assertSame(8.5, $sensor->sensor_limit_low);
    }
}

class ThresholdTestSensor
{
    use HasThresholds;

    public ?float $sensor_limit = null;
    public ?float $sensor_limit_warn = null;
    public ?float $sensor_limit_low = null;
    public ?float $sensor_limit_low_warn = null;

    public function __construct(
        public string $sensor_class,
        public ?float $sensor_current,
    ) {
    }
}
