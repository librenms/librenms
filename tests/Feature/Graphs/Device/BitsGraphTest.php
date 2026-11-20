<?php

namespace LibreNMS\Tests\Feature\Graphs\Device;

use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesSum;
use App\Facades\LibrenmsConfig;
use App\Graphs\Device\BitsGraph;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;

class BitsGraphTest extends TestCase
{
    use RefreshDatabase;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('webui.graph_stacked', false);
        LibrenmsConfig::set('device_traffic_iftype', ['/loopback/', '/l2vlan/']);
        LibrenmsConfig::set('device_traffic_descr', ['/vlan/']);
        $this->device = Device::factory()->create(['os' => 'linux']);
    }

    private function graph(): BitsGraph
    {
        return new BitsGraph(new GraphParameters(['type' => 'device_bits', 'device' => $this->device->device_id]));
    }

    /** @param array<string, mixed> $attributes */
    private function port(array $attributes = []): Port
    {
        return Port::factory()->for($this->device)->create(['ifIndex' => 1, 'ifType' => 'ethernetCsmacd', 'ifDescr' => 'eth0', 'ifName' => 'eth0', ...$attributes]);
    }

    public function test_it_resolves_the_modern_graph(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_bits', 'device' => $this->device->device_id]));

        $this->assertInstanceOf(BitsGraph::class, $graph);
        $this->assertSame($this->device->display . '::bits', $graph->getTitle());
    }

    public function test_it_stacks_in_above_and_out_below_the_axis_for_each_port(): void
    {
        $eth0 = $this->port();
        $eth1 = $this->port(['ifIndex' => 2, 'ifDescr' => 'eth1', 'ifName' => 'eth1']);

        $series = $this->graph()->getSeries();

        $this->assertSame(["in_$eth0->port_id", "out_$eth0->port_id", "in_$eth1->port_id", "out_$eth1->port_id", 'in', 'out', 'aggregate'], array_keys($series));
        $in = $series["in_$eth1->port_id"];
        $this->assertSame(['hostname' => $this->device->hostname, 'id' => $eth1->port_id], $in->metric->tags);
        $this->assertSame('INOCTETS', $in->field);
        $this->assertSame(8.0, $in->scale);
        $this->assertSame('eth1 In', $in->description);
        $this->assertTrue($in->style->stacked);
        $this->assertFalse($in->style->invert);
        $out = $series["out_$eth1->port_id"];
        $this->assertSame('OUTOCTETS', $out->field);
        $this->assertTrue($out->style->stacked);
        $this->assertTrue($out->style->invert);
    }

    public function test_it_adds_up_the_traffic_of_the_device(): void
    {
        $eth0 = $this->port();
        $eth1 = $this->port(['ifIndex' => 2, 'ifDescr' => 'eth1', 'ifName' => 'eth1']);

        $series = $this->graph()->getSeries();

        $this->assertInstanceOf(SeriesSum::class, $series['in']);
        $this->assertSame(["in_$eth0->port_id", "in_$eth1->port_id"], $series['in']->series);
        $this->assertTrue($series['in']->style->legendOnly);
        $this->assertInstanceOf(SeriesSum::class, $series['out']);
        $this->assertSame(["out_$eth0->port_id", "out_$eth1->port_id"], $series['out']->series);
        $this->assertTrue($series['out']->style->invert);
        $this->assertInstanceOf(SeriesSum::class, $series['aggregate']);
        $this->assertSame(['in', 'out'], $series['aggregate']->series);
    }

    public function test_it_leaves_out_ports_that_do_not_count_toward_device_traffic(): void
    {
        $eth0 = $this->port();
        $this->port(['ifType' => 'softwareLoopback', 'ifDescr' => 'lo', 'ifName' => 'lo']);
        $this->port(['ifDescr' => 'vlan100', 'ifName' => 'eth0.100']);
        $this->port(['ifDescr' => 'eth2', 'ifName' => 'eth2', 'disabled' => 1]);
        $this->port(['ifDescr' => 'eth3', 'ifName' => 'eth3', 'deleted' => 1]);

        $this->assertSame(["in_$eth0->port_id", "out_$eth0->port_id", 'in', 'out', 'aggregate'], array_keys($this->graph()->getSeries()));
    }

    public function test_asa_l2vlan_ports_count_toward_device_traffic(): void
    {
        $this->device->update(['os' => 'asa']);
        $inside = $this->port(['ifType' => 'l2vlan', 'ifDescr' => 'inside', 'ifName' => 'inside']);

        $this->assertSame(["in_$inside->port_id", "out_$inside->port_id", 'in', 'out', 'aggregate'], array_keys($this->graph()->getSeries()));
    }

    public function test_each_port_shows_in_and_out_together_and_the_totals_get_percentiles(): void
    {
        $this->port();
        $this->port(['ifIndex' => 2, 'ifDescr' => 'eth1', 'ifName' => 'eth1']);
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/port-id1.rrd');

        $command = app(GraphAccess::class)->trusted(fn () => $this->graph()->getDebugCommand());

        $this->assertMatchesRegularExpression("/'AREA:ds0#[0-9A-F]{6}:eth0 In +' .*'AREA:ds1drawn#[0-9A-F]{6}:eth0 Out +' .*'AREA:ds2#[0-9A-F]{6}:eth1 In +:STACK' .*'AREA:ds3drawn#[0-9A-F]{6}:eth1 Out +:STACK'/", $command);
        $this->assertStringContainsString("'CDEF:ds4=ds0,ds2,ADDNAN'", $command);
        $this->assertStringContainsString("'CDEF:ds5=ds1,ds3,ADDNAN'", $command);
        $this->assertStringContainsString("'CDEF:ds6=ds4,ds5,ADDNAN'", $command);
        $this->assertStringContainsString("'HRULE:0#00000000:Total In         '", $command);
        $this->assertStringContainsString("'VDEF:ds4pct=ds4,95,PERCENT'", $command);
        $this->assertStringContainsString("'VDEF:ds5pct=ds5,95,PERCENT'", $command);
        $this->assertStringNotContainsString('ds0pct', $command);
        $this->assertStringNotContainsString('ds6pct', $command);
    }

    public function test_it_reports_devices_without_ports(): void
    {
        $this->expectException(NoGraphData::class);

        app(GraphAccess::class)->trusted(fn () => $this->graph()->getDebugCommand());
    }
}
