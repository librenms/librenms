<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\Threshold;
use LibreNMS\Tests\TestCase;

class GraphOptionsTest extends TestCase
{
    public function test_threshold_bounds_leave_room_around_the_shown_thresholds(): void
    {
        $options = new GraphOptions(thresholds: [
            new Threshold(-20, 'low', SeriesRole::LowLimit),
            new Threshold(80, 'high', SeriesRole::HighLimit),
            new Threshold(1000, 'hidden', SeriesRole::HighWarning, shown: false),
        ]);

        $this->assertSame([-25.0, 85.0], $options->thresholdBounds());
        $this->assertCount(2, $options->shownThresholds());
    }

    public function test_the_room_does_not_cross_zero(): void
    {
        $this->assertSame([0.0, 86.0], new GraphOptions(thresholds: [new Threshold(2, 'low', SeriesRole::LowLimit), new Threshold(82, 'high', SeriesRole::HighLimit)])->thresholdBounds());
        $this->assertSame([-86.0, 0.0], new GraphOptions(thresholds: [new Threshold(-82, 'low', SeriesRole::LowLimit), new Threshold(-2, 'high', SeriesRole::HighLimit)])->thresholdBounds());
    }

    public function test_one_threshold_gets_room_from_its_value(): void
    {
        $this->assertSame([38.0, 42.0], new GraphOptions(thresholds: [new Threshold(40, 'high', SeriesRole::HighLimit)])->thresholdBounds());
        $this->assertSame([-0.05, 0.05], new GraphOptions(thresholds: [new Threshold(0, 'zero', SeriesRole::HighLimit)])->thresholdBounds(), 'a threshold at zero is not on the edge');
    }

    public function test_no_shown_thresholds_have_no_bounds(): void
    {
        $this->assertNull(new GraphOptions()->thresholdBounds());
        $this->assertNull(new GraphOptions(thresholds: [new Threshold(40, 'high', SeriesRole::HighLimit, shown: false)])->thresholdBounds());
    }
}
