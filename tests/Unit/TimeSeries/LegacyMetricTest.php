<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\LegacyMetric;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
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

    private function processor(): MetricIdentity
    {
        return new MetricIdentity('processor', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1]);
    }

    public function test_a_cleaned_up_metric_is_stored_under_the_names_it_had(): void
    {
        $legacy = LegacyMetric::legacy($this->processor());

        $this->assertNotNull($legacy);
        $this->assertSame('processors', $legacy->measurement);
        $this->assertSame(['processor_type' => 'hr', 'processor_index' => 1], $legacy->tagsOf($this->processor()));
        $this->assertSame(['processor', 'hr', '1'], $legacy->rrdNameOf($this->processor()));
    }

    public function test_canonical_metrics_are_stored_as_identified(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);

        $this->assertNull(LegacyMetric::legacy($this->processor()));
        $this->assertNotNull(LegacyMetric::previous($this->processor()));
    }

    public function test_a_metric_that_was_always_clean_has_no_other_names(): void
    {
        $this->assertNull(LegacyMetric::legacy(new MetricIdentity('vm.cpu', ['cluster' => 'proxmox-abc', 'id' => 100])));
    }
}
