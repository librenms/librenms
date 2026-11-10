<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\LegacyMetric;
use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Tests\TestCase;

class LegacyMetricTest extends TestCase
{
    public function test_only_the_tags_that_were_stored_are_kept(): void
    {
        $legacy = new LegacyMetric('things', ['index' => 'thing_index']);
        $metric = new MetricIdentity('thing', ['hostname' => 'localhost', 'index' => 0, 'type' => 'new']);

        $this->assertSame(['thing_index' => 0], $legacy->tagsOf($metric));
    }

    public function test_the_rrd_file_was_named_after_the_measurement_by_default(): void
    {
        $this->assertNull(new LegacyMetric('things')->rrdNameOf(new MetricIdentity('thing', ['hostname' => 'localhost'])));
    }

    public function test_tag_values_fill_the_rrd_name(): void
    {
        $legacy = new LegacyMetric('things', rrdName: ['thing', 'id{index}']);

        $this->assertSame(['thing', 'id7'], $legacy->rrdNameOf(new MetricIdentity('thing', ['hostname' => 'localhost', 'index' => 7])));
    }
}
