<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\MetricNames;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('datastores')]
final class MetricNamesTest extends TestCase
{
    private function processor(): MetricIdentity
    {
        return new MetricIdentity('processor', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1]);
    }

    public function testACleanedUpMetricIsStoredUnderTheNamesItHad(): void
    {
        $legacy = new MetricNames()->storedAs($this->processor());

        $this->assertNotNull($legacy);
        $this->assertSame('processors', $legacy->measurement);
        $this->assertSame(['processor_type' => 'hr', 'processor_index' => 1], $legacy->tagsOf($this->processor()));
        $this->assertSame(['processor', 'hr', '1'], $legacy->rrdNameOf($this->processor()));
    }

    public function testCanonicalMetricsAreStoredAsIdentified(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);
        $names = new MetricNames;

        $this->assertNull($names->storedAs($this->processor()));
        $this->assertNotNull($names->legacyOf($this->processor()));
    }

    public function testAMetricThatWasAlwaysCleanHasNoOtherNames(): void
    {
        $this->assertNull(new MetricNames()->storedAs(new MetricIdentity('vm.cpu', ['cluster' => 'proxmox-abc', 'id' => 100])));
    }
}
