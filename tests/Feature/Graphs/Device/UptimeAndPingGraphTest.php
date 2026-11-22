<?php

namespace LibreNMS\Tests\Feature\Graphs\Device;

use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\Device\IcmpPerfGraph;
use App\Graphs\Device\UptimeGraph;
use App\Graphs\GraphFactory;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class UptimeAndPingGraphTest extends TestCase
{
    use RefreshDatabase;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $this->device = Device::factory()->create();
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturnUsing(fn ($metric) => "host/$metric->measurement.rrd");
    }

    public function test_uptime_is_shown_in_days_without_prefixes(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_uptime', 'device' => $this->device->device_id]));
        $this->assertInstanceOf(UptimeGraph::class, $graph);

        $command = $graph->getDebugCommand();

        $this->assertStringContainsString('DEF:ds0raw=host/uptime.rrd:uptime:AVERAGE', $command);
        $this->assertStringContainsString('CDEF:ds0=ds0raw,0.0000115740740740741,*', $command);
        $this->assertStringContainsString("'--units-exponent' '0'", $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST:%5.2lf days', $command);
    }

    public function test_ping_draws_the_rtt_range_and_calculated_loss(): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_icmp_perf', 'device' => $this->device->device_id]));
        $this->assertInstanceOf(IcmpPerfGraph::class, $graph);

        $series = $graph->getSeries();
        $this->assertSame('ping', $series['rtt']->metric->measurement);
        $this->assertInstanceOf(Formula::class, $series['loss']->field);

        $command = $graph->getDebugCommand();
        $this->assertStringContainsString('DEF:ds0min=host/ping.rrd:min:MIN', $command);
        $this->assertStringContainsString('DEF:ds0max=host/ping.rrd:max:MAX', $command);
        $this->assertStringContainsString('AREA:ds0range#D0D0D0::STACK', $command);
        $this->assertStringContainsString('CDEF:ds1=ds10f,ds11f,-,ds10f,/,100,*', $command);
        $this->assertStringContainsString("'--right-axis' '1:0'", $command);
        $this->assertStringContainsString('GPRINT:ds1:LAST:%5.2lf %%', $command);
    }

    public function test_ping_follows_the_dark_style(): void
    {
        $command = new IcmpPerfGraph(new GraphParameters(['type' => 'device_icmp_perf', 'device' => $this->device->device_id, 'style' => 'dark']))->getDebugCommand();

        $this->assertStringContainsString('LINE1.25:ds0#F2F2F2:RTT', $command);
    }
}
