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

    public function testMempoolsAndSensorsKeepTheTagsTheyWereStoredWith(): void
    {
        $mempool = new MetricIdentity('mempool', ['hostname' => 'localhost', 'type' => 'hrstorage', 'class' => 'system', 'index' => 1]);
        $legacy = new MetricNames()->storedAs($mempool);
        $this->assertSame(['mempool_type' => 'hrstorage', 'mempool_class' => 'system', 'mempool_index' => 1], $legacy?->tagsOf($mempool));
        $this->assertSame(['mempool', 'hrstorage', 'system', '1'], $legacy->rrdNameOf($mempool));

        $sensor = new MetricIdentity('sensor', ['hostname' => 'localhost', 'class' => 'voltage', 'type' => 'rfc', 'index' => '7']);
        $legacy = new MetricNames()->storedAs($sensor);
        $this->assertSame(['sensor_class' => 'voltage', 'sensor_type' => 'rfc', 'sensor_index' => '7'], $legacy?->tagsOf($sensor));
        $this->assertSame(['sensor', 'voltage', 'rfc', '7'], $legacy->rrdNameOf($sensor));

        $ipmi = new MetricIdentity('sensor', ['hostname' => 'localhost', 'class' => 'voltage', 'type' => 'ipmi', 'descr' => 'PSU 1']);
        $legacy = new MetricNames()->storedAs($ipmi);
        $this->assertSame('ipmi', $legacy?->measurement);
        $this->assertSame(['sensor_class' => 'voltage', 'sensor_type' => 'ipmi', 'sensor_descr' => 'PSU 1'], $legacy->tagsOf($ipmi));
        $this->assertSame(['sensor', 'voltage', 'ipmi', 'PSU 1'], $legacy->rrdNameOf($ipmi));

        $ping = new MetricIdentity('ping', ['hostname' => 'localhost']);
        $legacy = new MetricNames()->storedAs($ping);
        $this->assertSame('icmp-perf', $legacy?->measurement);
        $this->assertNull($legacy->rrdNameOf($ping));
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
