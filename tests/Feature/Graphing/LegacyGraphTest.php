<?php

namespace LibreNMS\Tests\Feature\Graphing;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Graphs\Legacy\LegacyGraph;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Graph;
use Mockery;

class LegacyGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('allow_unauth_graphs', true);
    }

    public function test_auth_file_resolves_the_port_and_device(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'port_bits', 'id' => $port->port_id]));

        $this->assertInstanceOf(LegacyGraph::class, $graph);
        $this->assertTrue($graph->authorize());
        $this->assertEquals($port->port_id, $graph->getPageContext()->port?->port_id);
        $this->assertEquals($port->device_id, $graph->getPageContext()->device?->device_id);
    }

    public function test_guests_need_anonymous_graphs(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs', false);
        $device = Device::factory()->create();

        $this->assertFalse($this->deviceGraph($device)->authorize());
        $this->assertTrue(app(GraphAccess::class)->trusted(fn () => $this->deviceGraph($device)->authorize()));
    }

    public function test_users_need_permission_to_the_device(): void
    {
        $device = Device::factory()->create();
        $this->actingAs(User::factory()->create(['enabled' => 1]));

        $this->assertFalse($this->deviceGraph($device)->authorize());
    }

    public function test_alert_images_render_without_a_user(): void
    {
        LibrenmsConfig::set('allow_unauth_graphs', false);
        $device = Device::factory()->create();
        $backend = Mockery::mock(RrdBackendInterface::class);
        $backend->shouldReceive('graph')->once()->andReturn('image-data');
        $this->app->instance(RrdBackendInterface::class, $backend);

        $this->assertSame('image-data', Graph::getImage(['type' => 'device_ib_dns_performance', 'device' => $device->device_id, 'graph_type' => 'png'])->data);
        $this->assertFalse($this->deviceGraph($device)->authorize(), 'trust ends with the alert image');
    }

    public function test_graph_file_sets_title_and_options(): void
    {
        $device = Device::factory()->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_ib_dns_performance', 'id' => $device->device_id, 'title' => 'yes']));
        $this->assertInstanceOf(LegacyGraph::class, $graph);
        $options = $graph->getRrdCommandOptions();

        $this->assertContains('--vertical-label', $options);
        $this->assertContains('Answer time in milliseconds', $options);
        $this->assertContains('-l', $options);
        $this->assertContains('--title=' . $device->display, $options);
        $this->assertNotEmpty(preg_grep('/^DEF:PerfAA=/', $options));
    }

    public function test_user_title_replaces_the_graph_title(): void
    {
        $device = Device::factory()->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_ib_dns_performance', 'id' => $device->device_id, 'graph_title' => 'Custom']));

        $this->assertInstanceOf(LegacyGraph::class, $graph);
        $this->assertContains('--title=Custom', $graph->getRrdCommandOptions());
    }

    public function test_unexpected_errors_are_reported_without_showing_the_details(): void
    {
        Exceptions::fake();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'bill_bits']));

        try {
            $graph->authorize();
            $this->fail('Expected a graph error');
        } catch (GraphException $e) {
            $this->assertSame('Graph Error', $e->getMessage());
            $this->assertNotNull($e->getPrevious());
            Exceptions::assertReported($e->getPrevious()::class);
        }
    }

    public function test_title_is_available_before_the_rrd_options_are_built(): void
    {
        $device = Device::factory()->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_ib_dns_performance', 'id' => $device->device_id]));

        $this->assertSame($device->display, $graph->getTitle());
    }

    public function test_multi_bits_aggregate_requires_two_series(): void
    {
        Rrd::partialMock()->shouldReceive('checkRrdExists')->andReturn(true);
        $device = Device::factory()->create();
        $ports = Port::factory()->count(2)->for($device)->create();

        $this->assertEmpty($this->aggregateOptions((string) $ports[0]->port_id));
        $this->assertNotEmpty($this->aggregateOptions($ports->pluck('port_id')->implode(',')));
        $this->assertEmpty($this->aggregateOptions($ports->pluck('port_id')->implode(','), noagg: true));
    }

    private function deviceGraph(Device $device): LegacyGraph
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_ib_dns_performance', 'device' => $device->device_id]));
        $this->assertInstanceOf(LegacyGraph::class, $graph);

        return $graph;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function aggregateOptions(string $ids, bool $noagg = false): array
    {
        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'multiport_bits_separate', 'id' => $ids, 'noagg' => $noagg]));
        $this->assertInstanceOf(LegacyGraph::class, $graph);

        return preg_grep('/^CDEF:aggr/', array_map(strval(...), $graph->getRrdCommandOptions())) ?: [];
    }
}
