<?php

namespace LibreNMS\Tests\Feature\Graphing;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Graphs\Device\DeviceGraph;
use App\Models\Device;
use App\Models\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;

/**
 * Draws images with rrdtool from real rrd files
 */
class RrdGraphRenderingTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_HEADER = "\x89PNG";
    private string $rrdDir;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_executable((string) LibrenmsConfig::get('rrdtool', '/usr/bin/rrdtool'))) {
            $this->markTestSkipped('rrdtool is not installed');
        }

        $this->rrdDir = sys_get_temp_dir() . '/librenms-graph-' . uniqid();
        File::ensureDirectoryExists($this->rrdDir);
        LibrenmsConfig::set('rrd_dir', $this->rrdDir);
        LibrenmsConfig::set('rrdcached', '');
        LibrenmsConfig::set('rrd.heartbeat', 600);
        LibrenmsConfig::set('rrd_rra', 'RRA:AVERAGE:0.5:1:100 RRA:MIN:0.5:1:100 RRA:MAX:0.5:1:100');
        LibrenmsConfig::set('webui.graph_type', 'png');
        app()->forgetInstance(RrdBackendInterface::class);
    }

    protected function tearDown(): void
    {
        if (isset($this->rrdDir)) {
            File::deleteDirectory($this->rrdDir);
        }

        parent::tearDown();
    }

    public function test_it_draws_a_graph_class(): void
    {
        $device = Device::factory()->create();
        $this->createRrd($device, 'netstats-ip.rrd', ['ipForwDatagrams', 'ipInDelivers', 'ipInReceives', 'ipOutRequests', 'ipInDiscards', 'ipOutDiscards', 'ipOutNoRoutes']);

        $image = $this->graph('device_netstat_ip', $device)->render();

        $this->assertStringStartsWith(self::PNG_HEADER, $image->data);
    }

    public function test_it_draws_a_legacy_graph(): void
    {
        $device = Device::factory()->create();
        $this->createRrd($device, 'ib_dns_performance.rrd', ['PerfAA', 'PerfnonAA']);

        $image = $this->graph('device_ib_dns_performance', $device)->render();

        $this->assertStringStartsWith(self::PNG_HEADER, $image->data);
    }

    public function test_it_draws_stacked_series_with_the_previous_period(): void
    {
        $device = Device::factory()->create(['os' => 'linux']);
        LibrenmsConfig::set('os.linux.processor_stacked', true);
        foreach ([0, 1] as $index) {
            Processor::factory()->create(['device_id' => $device->device_id, 'processor_type' => 'hr', 'processor_index' => $index]);
            $this->createRrd($device, "processor-hr-$index.rrd", ['usage']);
        }

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id, 'graph_type' => 'png', 'previous' => 'yes']));

        $this->assertStringStartsWith(self::PNG_HEADER, $graph->render()->data);
    }

    public function test_it_draws_lines_with_a_scaled_series(): void
    {
        $device = Device::factory()->create();
        $this->createRrd($device, 'traffic.rrd', ['bytes']);

        $graph = new class(new GraphParameters(['type' => 'device_traffic', 'device' => $device->device_id, 'graph_type' => 'png'])) extends DeviceGraph
        {
            public function getSeries(): array
            {
                return ['bits' => new DataSeries(new MetricIdentity($this->params->deviceId, 'traffic'), 'bytes', 'Traffic', 'bps', 8, new SeriesStyle(fill: true, invert: true))];
            }
        };

        $this->assertStringStartsWith(self::PNG_HEADER, $graph->render()->data);
    }

    public function test_successful_legacy_draws_do_not_check_for_rrd_files(): void
    {
        $device = Device::factory()->create();
        $this->createRrd($device, 'ib_dns_performance.rrd', ['PerfAA', 'PerfnonAA']);
        Rrd::partialMock()->shouldReceive('checkRrdExists')->never();

        $image = $this->graph('device_ib_dns_performance', $device)->render();

        $this->assertStringStartsWith(self::PNG_HEADER, $image->data);
    }

    public function test_series_graphs_without_rrd_files_are_reported_as_no_data(): void
    {
        $device = Device::factory()->create();

        $this->expectException(NoGraphData::class);

        $this->graph('device_netstat_ip', $device)->render();
    }

    public function test_missing_rrd_files_are_reported_as_no_data(): void
    {
        $device = Device::factory()->create();

        $this->expectException(NoGraphData::class);

        $this->graph('device_ib_dns_performance', $device)->render();
    }

    private function graph(string $type, Device $device): GraphInterface
    {
        return (new GraphFactory)->graphFor(new GraphParameters(['type' => $type, 'device' => $device->device_id, 'graph_type' => 'png']));
    }

    /**
     * @param  string[]  $datasets
     */
    private function createRrd(Device $device, string $filename, array $datasets): void
    {
        $definition = RrdDefinition::make();
        foreach ($datasets as $dataset) {
            $definition->addDataset($dataset, 'GAUGE', 0);
        }

        File::ensureDirectoryExists("$this->rrdDir/$device->hostname");
        app(RrdBackendInterface::class)->create(RrdPath::make($device->hostname, $filename), $definition);
    }
}
