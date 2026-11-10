<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\MetricNaming;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;

class MetricNamingTest extends TestCase
{
    private function processor(): MetricIdentity
    {
        return new MetricIdentity('processor', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1]);
    }

    public function test_a_cleaned_up_metric_is_stored_under_the_names_it_had(): void
    {
        $legacy = new MetricNaming()->legacy($this->processor());

        $this->assertNotNull($legacy);
        $this->assertSame('processors', $legacy->measurement);
        $this->assertSame(['processor_type' => 'hr', 'processor_index' => 1], $legacy->tagsOf($this->processor()));
        $this->assertSame(['processor', 'hr', '1'], $legacy->rrdNameOf($this->processor()));
    }

    public function test_canonical_metrics_are_stored_as_identified(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);
        $naming = new MetricNaming;

        $this->assertNull($naming->legacy($this->processor()));
        $this->assertNotNull($naming->previous($this->processor()));
    }

    public function test_a_metric_that_was_always_clean_has_no_other_names(): void
    {
        $this->assertNull(new MetricNaming()->legacy(new MetricIdentity('vm.cpu', ['cluster' => 'proxmox-abc', 'id' => 100])));
    }
}
