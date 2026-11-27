<?php

namespace LibreNMS\Tests\Feature\Graphs\Multiport;

use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesSum;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphFactory;
use App\Graphs\Multiport\BitsGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\InvalidGraphInput;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class BitsGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
    }

    private function graph(string $ids, int $width = 400): BitsGraph
    {
        return new BitsGraph(new GraphParameters(['type' => 'multiport_bits', 'id' => $ids, 'width' => $width]));
    }

    #[TestWith(['multiport_bits'])]
    #[TestWith(['multiport_bits_separate'])]
    public function test_it_resolves_the_modern_graph(string $type): void
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => $type, 'id' => '1,2']));

        $this->assertInstanceOf(BitsGraph::class, $graph);
        $this->assertSame('bits', new GraphParameters(['type' => $type])->subtype);
        $this->assertSame('Multi Port', $graph->getTitle());
    }

    public function test_the_old_separate_type_is_not_listed_as_a_subtype(): void
    {
        $this->assertSame(['bits'], (new GraphFactory)->subtypes('multiport'));
    }

    public function test_the_heading_counts_the_ports_and_their_devices(): void
    {
        $first = Device::factory()->create(['hostname' => 'first.example.com']);
        $ports = Port::factory()->count(2)->for($first)->create();
        $other = Port::factory()->for(Device::factory())->create();

        $this->assertSame('Multi Port', $this->graph('')->getPageContext()->subtitle);
        $this->assertSame('Multi Port :: 1 port on first', $this->graph((string) $ports[0]->port_id)->getPageContext()->subtitle);
        $this->assertSame('Multi Port :: 2 ports on first', $this->graph($ports->pluck('port_id')->implode(','))->getPageContext()->subtitle);
        $this->assertSame('Multi Port :: 3 ports on 2 devices', $this->graph($ports->pluck('port_id')->push($other->port_id)->implode(','))->getPageContext()->subtitle);
    }

    public function test_it_stacks_the_requested_ports_in_order_with_their_devices(): void
    {
        $first = Port::factory()->for(Device::factory()->create(['hostname' => 'first.example.com']))->create(['ifName' => 'eth0', 'ifDescr' => 'eth0']);
        $second = Port::factory()->for(Device::factory()->create(['hostname' => 'second.example.com']))->create(['ifName' => 'eth1', 'ifDescr' => 'eth1']);

        $series = $this->graph("$second->port_id,$first->port_id,$second->port_id")->getSeries();

        $this->assertSame(["in_$second->port_id", "out_$second->port_id", "in_$first->port_id", "out_$first->port_id", 'in', 'out', 'aggregate'], array_keys($series));
        $in = $series["in_$second->port_id"];
        $this->assertSame(['hostname' => 'second.example.com', 'id' => $second->port_id], $in->metric->tags);
        $this->assertSame('INOCTETS', $in->field);
        $this->assertSame($second->device->shortDisplayName() . ' eth1 In', $in->description);
        $this->assertTrue($in->style->stacked);
        $this->assertTrue($series["out_$first->port_id"]->style->invert);
        $this->assertInstanceOf(SeriesSum::class, $series['in']);
        $this->assertSame(["in_$second->port_id", "in_$first->port_id"], $series['in']->series);
    }

    #[TestWith([300, 'first Gi1/0/11 I'])]
    #[TestWith([700, 'first Gi1/0/11 In'])]
    public function test_wider_graphs_have_room_for_longer_labels(int $width, string $label): void
    {
        $port = Port::factory()->for(Device::factory()->create(['hostname' => 'first.example.com']))->create(['ifName' => 'GigabitEthernet1/0/11', 'ifDescr' => 'GigabitEthernet1/0/11']);
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/port.rrd');

        $command = $this->graph((string) $port->port_id, $width)->getDebugCommand();

        $this->assertMatchesRegularExpression('/\'AREA:ds0#[0-9A-F]{6}:' . preg_quote($label, '/') . ' *\'/', $command);
    }

    public function test_inverse_swaps_in_and_out(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $series = new BitsGraph(new GraphParameters(['type' => 'multiport_bits', 'id' => $port->port_id, 'inverse' => 1]))->getSeries();

        $this->assertSame('OUTOCTETS', $series["in_$port->port_id"]->field);
        $this->assertSame('INOCTETS', $series["out_$port->port_id"]->field);
    }

    public function test_every_port_has_to_exist(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $this->assertTrue($this->graph((string) $port->port_id)->authorize());
        $this->assertFalse($this->graph($port->port_id . ',' . PHP_INT_MAX)->authorize());
    }

    public function test_every_port_has_to_be_viewable(): void
    {
        $ports = Port::factory()->count(2)->for(Device::factory())->create();
        $this->actingAs(User::factory()->create());
        Gate::shouldReceive('allows')->andReturnUsing(fn (string $ability, Port $port) => $port->port_id === $ports[0]->port_id);

        $this->assertTrue($this->graph((string) $ports[0]->port_id)->authorize());
        $this->assertFalse($this->graph($ports->pluck('port_id')->implode(','))->authorize());
    }

    public function test_guests_require_anonymous_graphs(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs', false);
        $port = Port::factory()->for(Device::factory())->create();

        $this->assertFalse($this->graph((string) $port->port_id)->authorize());
    }

    public function test_no_ports_report_no_data(): void
    {
        $this->expectException(NoGraphData::class);

        $this->graph('')->getDebugCommand();
    }

    public function test_it_rejects_ids_that_are_not_port_ids(): void
    {
        $this->expectException(InvalidGraphInput::class);

        $this->graph('1,abc')->getDebugCommand();
    }
}
