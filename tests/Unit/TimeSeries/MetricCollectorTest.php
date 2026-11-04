<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\MetricCollector;
use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Interfaces\Models\HasMetrics;
use LibreNMS\Tests\TestCase;

final class MetricCollectorTest extends TestCase
{
    public function testRecordsTheIdentityOfTheModel(): void
    {
        $collector = new MetricCollector(['vm.cpu']);

        $collector->record($this->model(), 'vm.cpu', ['usage' => 25]);

        $entries = $collector->entries();
        $this->assertCount(1, $entries);
        $this->assertSame('vm.cpu', $entries[0]->identity->measurement);
        $this->assertSame(['usage' => 25], $entries[0]->fields);
    }

    public function testRejectsMeasurementsThatAreNotAllowed(): void
    {
        $this->expectException(\RuntimeException::class);

        (new MetricCollector(['vm.cpu']))->record($this->model(), 'vm.unknown', ['usage' => 25]);
    }

    private function model(): HasMetrics
    {
        return new class implements HasMetrics
        {
            public function metricIdentity(string $measurement): MetricIdentity
            {
                return new MetricIdentity('host.example.com', $measurement);
            }
        };
    }
}
