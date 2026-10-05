<?php

namespace LibreNMS\Tests\Unit\Services;

use App\Services\LiveSnmpMetricService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class LiveSnmpMetricServiceTest extends TestCase
{
    #[Test]
    public function cpu_busy_and_wait_share_the_complete_tick_denominator(): void
    {
        [$cpu, $wait] = $this->invoke('cpuMetrics', [
            ['user' => '1000', 'nice' => '1000', 'system' => '1000', 'idle' => '1000', 'wait' => '1000'],
            ['user' => '1020', 'nice' => '1000', 'system' => '1005', 'idle' => '1050', 'wait' => '1025'],
        ]);

        $this->assertSame(25.0, $cpu['value']);
        $this->assertSame(25.0, $wait['value']);
    }

    #[Test]
    public function network_uses_the_actual_counter_window(): void
    {
        $network = $this->invoke('networkMetrics', [
            ['7' => ['in' => '1000', 'out' => '2000']],
            ['7' => ['in' => '10001000', 'out' => '20002000']],
            [['port_id' => 42, 'if_index' => 7, 'name' => 'eth0']],
            10.0,
        ]);

        $this->assertSame(8_000_000.0, $network['in_bps']);
        $this->assertSame(16_000_000.0, $network['out_bps']);
        $this->assertSame([['port_id' => 42, 'name' => 'eth0', 'in_bps' => 8_000_000.0, 'out_bps' => 16_000_000.0]], $network['interfaces']);
    }

    #[Test]
    public function unchanged_agent_counters_are_not_a_valid_baseline(): void
    {
        $current = ['timestamp' => 20.0, 'network' => ['7' => ['in' => '1000', 'out' => '2000']]];
        $unchanged = ['timestamp' => 10.0, 'network' => ['7' => ['in' => '1000', 'out' => '2000']]];
        $changed = ['timestamp' => 8.0, 'network' => ['7' => ['in' => '900', 'out' => '1900']]];

        $this->assertNull($this->invoke('selectBaseline', [[$unchanged], $current, 10.0, 'network']));
        $this->assertSame($changed, $this->invoke('selectBaseline', [[$changed, $unchanged], $current, 10.0, 'network']));
    }

    #[Test]
    public function host_resources_cpu_averages_the_discovered_processors(): void
    {
        $metric = $this->invoke('hostResourcesCpuMetrics', [
            [
                '.1.3.6.1.2.1.25.3.3.1.2.1' => '59',
                '.1.3.6.1.2.1.25.3.3.1.2.2' => '13',
                '.1.3.6.1.2.1.25.3.3.1.2.3' => '14',
                '.1.3.6.1.2.1.25.3.3.1.2.4' => '15',
            ],
            [
                '.1.3.6.1.2.1.25.3.3.1.2.1',
                '.1.3.6.1.2.1.25.3.3.1.2.2',
                '.1.3.6.1.2.1.25.3.3.1.2.3',
                '.1.3.6.1.2.1.25.3.3.1.2.4',
            ],
        ]);

        $this->assertSame(25.25, $metric['value']);
        $this->assertSame(4, $metric['sampled_processor_count']);
    }

    #[Test]
    public function host_resources_memory_uses_allocation_units_size_and_used(): void
    {
        $metric = $this->invoke('hostResourcesMemoryMetrics', [
            [
                '.1.3.6.1.2.1.25.2.3.1.4.65536' => '1024',
                '.1.3.6.1.2.1.25.2.3.1.5.65536' => '3997696',
                '.1.3.6.1.2.1.25.2.3.1.6.65536' => '237588',
            ],
            [
                'allocation_units' => '.1.3.6.1.2.1.25.2.3.1.4.65536',
                'size' => '.1.3.6.1.2.1.25.2.3.1.5.65536',
                'used' => '.1.3.6.1.2.1.25.2.3.1.6.65536',
            ],
        ]);

        $this->assertSame(5.9431, $metric['value']);
        $this->assertSame(243290112, $metric['used_bytes']);
        $this->assertSame(4093640704, $metric['total_bytes']);
    }

    public function test_disk_rates_use_elapsed_time_and_accept_idle_disks(): void
    {
        $disks = [['diskio_index' => 1, 'diskio_descr' => 'sda']];
        $before = [1 => ['read' => '100', 'write' => '200', 'reads' => '10', 'writes' => '20', 'busy' => '0']];
        $after = [1 => ['read' => '1100', 'write' => '2200', 'reads' => '20', 'writes' => '40', 'busy' => '1000000']];
        $metric = $this->invoke('diskMetrics', [$before, $after, $disks, 5.0]);
        $this->assertSame(200.0, $metric['read_bytes_per_second']);
        $this->assertSame(400.0, $metric['write_bytes_per_second']);
        $this->assertSame(2.0, $metric['read_iops']);
        $this->assertSame(20.0, $metric['utilization_percent']);
        $idle = $this->invoke('diskMetrics', [$before, $before, $disks, 5.0]);
        $this->assertTrue($idle['available']);
        $this->assertSame(0.0, $idle['read_bytes_per_second']);
        $reset = $this->invoke('diskMetrics', [$after, $before, $disks, 5.0]);
        $this->assertFalse($reset['available']);
        $baseline = ['timestamp' => 10, 'disk' => $before];
        $this->assertSame($baseline, $this->invoke('selectBaseline', [[$baseline], ['timestamp' => 15, 'disk' => $before], 3.0, 'disk']));
    }

    public function test_whole_disks_exclude_partitions_and_stacked_devices(): void
    {
        $rows = array_map(fn ($name) => ['diskio_descr' => $name], ['sda', 'sda1', 'nvme0n1', 'nvme0n1p1', 'md0', 'bcache0']);
        $this->assertSame(['sda', 'nvme0n1'], array_column(LiveSnmpMetricService::selectDisks($rows), 'diskio_descr'));
        $this->assertSame([['diskio_descr' => 'md0']], LiveSnmpMetricService::selectDisks([['diskio_descr' => 'md0']]));
    }

    public function test_load_average_is_not_a_percentage(): void
    {
        $metric = $this->invoke('loadMetrics', [['.1.3.6.1.4.1.2021.10.1.5.1' => '12345']]);
        $this->assertSame(123.45, $metric['one']);
        $this->assertNull($metric['five']);
    }

    public function test_optional_steal_is_included_in_cpu_denominator(): void
    {
        $before = array_fill_keys(['user', 'nice', 'system', 'idle', 'wait', 'steal'], '100');
        $after = ['user' => '120', 'nice' => '105', 'system' => '115', 'idle' => '140', 'wait' => '110', 'steal' => '110'];
        [$cpu, $wait] = $this->invoke('cpuMetrics', [$before, $after]);
        $this->assertSame(25.0, $cpu['user']);
        $this->assertSame(15.0, $cpu['system']);
        $this->assertSame(10.0, $cpu['steal']);
        $this->assertSame(50.0, $cpu['value']);
        $this->assertSame(10.0, $wait['value']);
    }

    private function invoke(string $method, array $arguments): mixed
    {
        return (new ReflectionMethod(LiveSnmpMetricService::class, $method))
            ->invokeArgs(new LiveSnmpMetricService, $arguments);
    }
}
