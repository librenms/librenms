<?php

namespace LibreNMS\Tests\Feature\Graphs\Port;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Graphs\Port\BitsGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use Mockery;

class BitsGraphTest extends TestCase
{
    use RefreshDatabase;

    private Port $port;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        LibrenmsConfig::set('webui.graph_stacked', false);
        LibrenmsConfig::set('graphs.port_speed_zoom', false);
        LibrenmsConfig::set('percentile_value', 95);
        LibrenmsConfig::set('rrdgraph_real_percentile', false);
        $this->port = Port::factory()->for(Device::factory())->create([
            'ifDescr' => 'GigabitEthernet0/1', 'ifAlias' => 'Uplink', 'ifSpeed' => 1000000000,
        ]);
    }

    /** @param array<string, mixed> $params */
    private function graph(array $params = []): BitsGraph
    {
        return new BitsGraph(new GraphParameters(['type' => 'port_bits', 'id' => $this->port->port_id, ...$params]));
    }

    /** @param array<string, mixed> $params */
    private function command(array $params = []): string
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/port-id1.rrd');

        return $this->graph($params)->getDebugCommand();
    }

    public function test_it_resolves_the_modern_graph_and_port_context(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'port_bits', 'port' => $this->port->port_id]));

        $this->assertInstanceOf(BitsGraph::class, $graph);
        $this->assertSame($this->port->port_id, $graph->getPageContext()->port->port_id);
        $this->assertSame($this->port->device_id, $graph->getPageContext()->device->device_id);
        $this->assertSame($this->port->device->shortDisplayName() . '::gi0/1::Uplink', $graph->getTitle());
    }

    public function test_it_converts_octets_to_bits_with_positive_legend_values(): void
    {
        $series = $this->graph()->getSeries();

        $this->assertSame(['in', 'out'], array_keys($series));
        $this->assertSame('port', $series['in']->metric->measurement);
        $this->assertSame(['hostname' => $this->port->device->hostname, 'id' => $this->port->port_id], $series['in']->metric->tags);
        $this->assertSame('INOCTETS', $series['in']->field);
        $this->assertSame('OUTOCTETS', $series['out']->field);
        $this->assertSame(8.0, $series['out']->scale);
        $command = $this->command();
        $this->assertStringContainsString('CDEF:ds0=ds0raw,8,*', $command);
        $this->assertStringContainsString('CDEF:ds1drawn=ds1,-1,*', $command);
        $this->assertStringContainsString('AREA:ds1maxdrawn#E0E0FF', $command);
        $this->assertStringContainsString('GPRINT:ds1:LAST:', $command);
    }

    public function test_inverse_swaps_datasets(): void
    {
        $series = $this->graph(['inverse' => true])->getSeries();

        $this->assertSame('OUTOCTETS', $series['in']->field);
        $this->assertSame('INOCTETS', $series['out']->field);
        $this->assertTrue($series['out']->style->invert);
    }

    public function test_it_preserves_percentiles_byte_totals_and_port_speed(): void
    {
        LibrenmsConfig::set('percentile_value', 99);
        LibrenmsConfig::set('rrdgraph_real_percentile', true);
        $command = $this->command();

        $this->assertStringContainsString('VDEF:ds0pct=ds0,99,PERCENT', $command);
        $this->assertStringContainsString('CDEF:ds1pctdrawn=ds1,POP,ds1pct,-1,*', $command);
        $this->assertStringContainsString('HRULE:ds1pctline#aa0000', $command);
        $this->assertStringContainsString('CDEF:ds0bytes=ds0,0.125,*', $command);
        $this->assertStringContainsString('VDEF:ds0total=ds0bytes,TOTAL', $command);
        $this->assertStringContainsString('GPRINT:trafficbytes:Total %6.2lf%sB', $command);
        $this->assertStringContainsString('VDEF:trafficpct=traffichigh,99,PERCENT', $command);
        $this->assertStringContainsString('HRULE:1000000000#000000:Port Speed 1 Gbps', $command);
    }

    public function test_previous_period_is_shifted_scaled_and_summarized(): void
    {
        $command = $this->command(['from' => 100, 'to' => 200, 'previous' => 'yes']);

        $this->assertStringContainsString('DEF:ds0prevraw=host/port-id1.rrd:INOCTETS:AVERAGE:start=0:end=100', $command);
        $this->assertStringContainsString('SHIFT:ds0prevraw:100', $command);
        $this->assertStringContainsString('CDEF:ds1prev=ds1prevraw,8,*', $command);
        $this->assertStringContainsString('LINE1.25:ds1prevdrawn#000099:Prev Out', $command);
        $this->assertStringContainsString('GPRINT:trafficprevbytes:Prev Total', $command);
    }

    public function test_stacking_and_speed_zoom_draw_both_directions_above_zero(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', true);
        $this->assertFalse($this->graph()->getSeries()['out']->style->invert);
        LibrenmsConfig::set('webui.graph_stacked', false);

        $command = $this->command(['port_speed_zoom' => true]);

        $this->assertStringNotContainsString('CDEF:ds1drawn=', $command);
        $this->assertStringContainsString('AREA:ds1#8080C088', $command);
        $this->assertStringContainsString('LINE2:1000000000#000000:Port Speed', $command);
    }

    public function test_future_ranges_include_linear_prediction(): void
    {
        $command = $this->command(['to' => time() + 3600]);

        $this->assertStringContainsString('VDEF:ds0slope=ds0max,LSLSLOPE', $command);
        $this->assertStringContainsString('CDEF:ds1trend=ds1max,POP,ds1slope,COUNT,*,ds1intercept,+,-1,*', $command);
    }

    public function test_asymmetric_circuit_speeds_follow_inverse_mode(): void
    {
        $this->port->update(['port_descr_speed' => '100M/20M']);
        $command = $this->command();

        $this->assertStringContainsString('HRULE:20000000#000000:In Port Speed', $command);
        $this->assertStringContainsString('HRULE:-100000000#000000:Out Port Speed', $command);
        $inverse = $this->command(['inverse' => true]);
        $this->assertStringContainsString('HRULE:100000000#000000:In Port Speed', $inverse);
        $this->assertStringContainsString('HRULE:-20000000#000000:Out Port Speed', $inverse);
    }

    public function test_request_can_disable_configured_speed_zoom(): void
    {
        LibrenmsConfig::set('graphs.port_speed_zoom', true);
        $command = $this->command(['port_speed_zoom' => false, 'nototal' => true]);

        $this->assertStringContainsString('CDEF:ds1drawn=ds1,-1,*', $command);
        $this->assertStringContainsString('HRULE:1000000000#000000:Port Speed', $command);
        $this->assertStringNotContainsString('GPRINT:trafficbytes:', $command);
    }

    public function test_it_checks_port_permissions_even_when_an_unrelated_device_is_requested(): void
    {
        $this->actingAs(User::factory()->create());
        Gate::shouldReceive('allows')->with('view', Mockery::on(fn ($port) => $port instanceof Port && $port->port_id === $this->port->port_id))->once()->andReturn(false);

        $this->assertFalse($this->graph(['device' => Device::factory()->create()->device_id])->authorize());
    }

    public function test_port_permission_is_sufficient_without_device_permission(): void
    {
        $this->actingAs(User::factory()->create());
        Gate::shouldReceive('allows')->with('view', Mockery::type(Port::class))->once()->andReturn(true);

        $this->assertTrue($this->graph()->authorize());
    }

    public function test_guests_require_anonymous_graphs_or_a_trusted_caller(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs', false);

        $this->assertFalse($this->graph()->authorize());
        $this->assertTrue(app(GraphAccess::class)->trusted(fn () => $this->graph()->authorize()));
    }

    public function test_missing_ports_are_not_authorized(): void
    {
        $this->assertFalse($this->graph(['id' => PHP_INT_MAX])->authorize());
    }

    public function test_missing_rrd_files_report_no_data(): void
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturnNull();
        $this->expectException(NoGraphData::class);

        $this->graph()->getDebugCommand();
    }
}
