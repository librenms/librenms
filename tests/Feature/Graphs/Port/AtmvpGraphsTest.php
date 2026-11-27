<?php

namespace LibreNMS\Tests\Feature\Graphs\Port;

use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesRole;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Graphs\GraphFactory;
use App\Graphs\Port\AtmvpBitsGraph;
use App\Graphs\Port\AtmvpCellsGraph;
use App\Graphs\Port\AtmvpErrorsGraph;
use App\Graphs\Port\AtmvpGraph;
use App\Graphs\Port\AtmvpPacketsGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\JuniAtmVp;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\InvalidGraphInput;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class AtmvpGraphsTest extends TestCase
{
    use RefreshDatabase;

    private Port $port;
    private JuniAtmVp $vp;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $device = Device::factory()->create(['hostname' => 'erx.example.com']);
        $this->port = Port::factory()->for($device)->create(['ifIndex' => 42, 'ifDescr' => 'ATM2/0']);
        $this->vp = JuniAtmVp::factory()->create(['port_id' => $this->port->port_id, 'vp_id' => 7]);
    }

    /**
     * @param  array<string, mixed>  $vars
     */
    private function params(string $subtype, array $vars = []): GraphParameters
    {
        return new GraphParameters(['type' => "port_atmvp_$subtype", 'id' => $this->port->port_id, 'vp' => 7, ...$vars]);
    }

    #[TestWith(['bits', AtmvpBitsGraph::class, 'inpacketoctets', 'outpacketoctets', 'bps', 8])]
    #[TestWith(['packets', AtmvpPacketsGraph::class, 'inpackets', 'outpackets', 'pps', 1])]
    #[TestWith(['cells', AtmvpCellsGraph::class, 'incells', 'outcells', '', 1])]
    #[TestWith(['errors', AtmvpErrorsGraph::class, 'inpacketerrors', 'outpacketerrors', 'pps', 1])]
    public function test_each_graph_draws_its_pair_of_fields(string $subtype, string $class, string $in, string $out, string $unit, float $scale): void
    {
        $graph = (new GraphFactory)->graphFor($this->params($subtype));
        $this->assertInstanceOf($class, $graph);
        $this->assertInstanceOf(AtmvpGraph::class, $graph);

        $series = $graph->getSeries();

        $this->assertSame(['in', 'out'], array_keys($series));
        $this->assertSame($in, $series['in']->field);
        $this->assertSame($out, $series['out']->field);
        $this->assertSame($unit, $series['in']->unit);
        $this->assertSame($scale, $series['in']->scale);
        $this->assertSame(['hostname' => 'erx.example.com', 'ifIndex' => 42, 'vp' => 7], $series['in']->metric->tags);
        $this->assertFalse($series['in']->style->invert);
        $this->assertTrue($series['out']->style->invert);
    }

    public function test_the_subtypes_are_listed_with_the_port_graphs(): void
    {
        $subtypes = (new GraphFactory)->subtypes('port');

        foreach (['atmvp_bits', 'atmvp_cells', 'atmvp_errors', 'atmvp_packets'] as $subtype) {
            $this->assertContains($subtype, $subtypes);
        }
        $this->assertNotContains('atmvp', $subtypes);
    }

    public function test_it_reads_the_file_the_poller_writes(): void
    {
        $metric = $this->vp->load('port.device')->metricIdentity('atm_vp');

        $this->assertContains('erx.example.com/vp-42-7.rrd', array_map(fn ($path) => $path->relativePath(), Rrd::metricNames($metric)));
    }

    public function test_the_command_draws_traffic_from_the_vp_file(): void
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('erx.example.com/vp-42-7.rrd');

        $command = new AtmvpBitsGraph($this->params('bits'))->getDebugCommand();

        $this->assertStringContainsString('DEF:ds0raw=erx.example.com/vp-42-7.rrd:inpacketoctets:AVERAGE', $command);
        $this->assertStringContainsString('DEF:ds1raw=erx.example.com/vp-42-7.rrd:outpacketoctets:AVERAGE', $command);
        $this->assertStringContainsString('CDEF:ds0=ds0raw,8,*', $command);
        $this->assertStringContainsString('HRULE:ds0p0#', $command);
    }

    public function test_errors_use_the_error_role(): void
    {
        $series = new AtmvpErrorsGraph($this->params('errors'))->getSeries();

        $this->assertSame(SeriesRole::Error, $series['in']->style->role);
        $this->assertSame(SeriesRole::Error, $series['out']->style->role);
    }

    public function test_inverse_swaps_directions(): void
    {
        $series = new AtmvpCellsGraph($this->params('cells', ['inverse' => true]))->getSeries();

        $this->assertSame('outcells', $series['in']->field);
        $this->assertSame('incells', $series['out']->field);
    }

    public function test_the_title_and_heading_name_the_vp(): void
    {
        $graph = new AtmvpBitsGraph($this->params('bits'));

        $this->assertSame('erx.example::atm2/0::vp7', $graph->getTitle());
        $context = $graph->getPageContext();
        $this->assertSame($this->port->port_id, $context->port?->port_id);
        $this->assertSame($this->port->device_id, $context->device?->device_id);
        $this->assertSame(' :: ATM VP :: 7', $context->subtitle);
    }

    public function test_the_vp_has_to_belong_to_the_port(): void
    {
        $other = Port::factory()->for(Device::factory())->create();

        $this->assertTrue(new AtmvpBitsGraph($this->params('bits'))->authorize());
        $this->assertFalse(new AtmvpBitsGraph($this->params('bits', ['vp' => 8]))->authorize());
        $this->assertFalse(new AtmvpBitsGraph($this->params('bits', ['id' => $other->port_id]))->authorize());
    }

    public function test_the_port_has_to_be_viewable(): void
    {
        $this->actingAs(User::factory()->create());
        Gate::shouldReceive('allows')->andReturn(false);

        $this->assertFalse(new AtmvpPacketsGraph($this->params('packets'))->authorize());
    }

    public function test_it_rejects_a_missing_vp(): void
    {
        $this->expectException(InvalidGraphInput::class);

        new AtmvpPacketsGraph(new GraphParameters(['type' => 'port_atmvp_packets', 'id' => $this->port->port_id]))->getDebugCommand();
    }
}
