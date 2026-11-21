<?php

namespace LibreNMS\Tests\Feature\Graphs\Port;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphFactory;
use App\Graphs\Port\ErrorsGraph;
use App\Graphs\Port\UpktsGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class PacketGraphsTest extends TestCase
{
    use RefreshDatabase;

    private Port $port;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        LibrenmsConfig::set('webui.graph_stacked', false);
        $this->port = Port::factory()->for(Device::factory())->create();
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/port-id1.rrd');
    }

    public function test_unicast_packets_draw_out_below_the_axis(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'port_upkts', 'id' => $this->port->port_id]));
        $this->assertInstanceOf(UpktsGraph::class, $graph);

        $command = $graph->getDebugCommand();

        $this->assertStringContainsString('DEF:ds0=host/port-id1.rrd:INUCASTPKTS:AVERAGE', $command);
        $this->assertStringContainsString('DEF:ds1=host/port-id1.rrd:OUTUCASTPKTS:AVERAGE', $command);
        $this->assertStringContainsString('AREA:ds1maxdrawn#FFEFAA', $command);
        $this->assertStringContainsString('GPRINT:ds1:LAST:%5.2lf%spps', $command);
    }

    public function test_inverse_swaps_directions(): void
    {
        $series = new UpktsGraph(new GraphParameters(['type' => 'port_upkts', 'id' => $this->port->port_id, 'inverse' => true]))->getSeries();

        $this->assertSame('OUTUCASTPKTS', $series['in']->field);
        $this->assertSame('INUCASTPKTS', $series['out']->field);
    }

    public function test_errors_and_discards_stack_in_each_direction(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'port_errors', 'id' => $this->port->port_id]));
        $this->assertInstanceOf(ErrorsGraph::class, $graph);

        $command = $graph->getDebugCommand();

        $this->assertStringContainsString("'AREA:ds0#FF3300:Errors In        '", $command);
        $this->assertStringContainsString("'AREA:ds1#805080:Discards In      :STACK'", $command);
        $this->assertStringContainsString("'AREA:ds2drawn#FF6633:Errors Out       '", $command);
        $this->assertStringContainsString("'AREA:ds3drawn#C0A060:Discards Out     :STACK'", $command);
    }

    public function test_stacked_graphs_draw_everything_above_the_axis(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', true);

        $command = new ErrorsGraph(new GraphParameters(['type' => 'port_errors', 'id' => $this->port->port_id]))->getDebugCommand();

        $this->assertStringNotContainsString('drawn', $command);
        $this->assertStringContainsString("'AREA:ds2#FF663388:Errors Out       :STACK'", $command);
    }
}
