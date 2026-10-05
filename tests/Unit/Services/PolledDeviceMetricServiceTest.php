<?php

namespace LibreNMS\Tests\Unit\Services;

use App\Services\PolledDeviceMetricService;
use App\Services\RrdSample;
use PHPUnit\Framework\TestCase;

class PolledDeviceMetricServiceTest extends TestCase
{
    public function test_cpu_requires_aligned_samples_and_preserves_missing_steal(): void
    {
        $points = [];
        foreach (['user' => 10, 'nice' => 5, 'system' => 5, 'idle' => 75, 'wait' => 5] as $state => $rate) {
            $points[$state] = new RrdSample(1000, ['value' => $rate]);
        }
        $points['steal'] = null;
        $result = PolledDeviceMetricService::cpuStates($points);
        $this->assertSame(20.0, $result['cpu']['value']);
        $this->assertSame(15.0, $result['cpu']['user']);
        $this->assertSame(5.0, $result['io_wait']['value']);
        $this->assertNull($result['cpu']['steal']);
        $points['wait'] = new RrdSample(700, ['value' => 5]);
        $this->assertNull(PolledDeviceMetricService::cpuStates($points));
    }

    public function test_disk_rrd_values_are_already_rates_and_zero_is_valid(): void
    {
        $sample = [];
        foreach (['read' => 1024, 'written' => 0, 'reads' => 2, 'writes' => 0] as $ds => $rate) {
            $sample[$ds] = new RrdSample(1000, [$ds => $rate]);
        }
        $result = PolledDeviceMetricService::diskRates([$sample, $sample]);
        $this->assertTrue($result['available']);
        $this->assertFalse($result['partial']);
        $this->assertEquals(2048, $result['read_bytes_per_second']);
        $this->assertEquals(0, $result['write_bytes_per_second']);
        $this->assertEquals(4, $result['read_iops']);
        unset($sample['reads']);
        $this->assertNull(PolledDeviceMetricService::diskRates([$sample])['read_iops']);
        $this->assertFalse(PolledDeviceMetricService::diskRates([[]])['available']);
        $sample['written'] = new RrdSample(700, ['written' => 2]);
        $this->assertFalse(PolledDeviceMetricService::diskRates([$sample])['available']);
    }
}
