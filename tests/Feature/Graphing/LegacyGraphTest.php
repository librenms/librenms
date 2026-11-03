<?php

namespace LibreNMS\Tests\Feature\Graphing;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Legacy\LegacyGraph;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Interfaces\Data\Graphing\RrdGraphInterface;
use LibreNMS\Tests\TestCase;

class LegacyGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_file_resolves_the_port_and_device(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'port_bits', 'id' => $port->port_id]));

        $this->assertInstanceOf(LegacyGraph::class, $graph);
        $this->assertTrue($graph->authorize());
        $this->assertEquals($port->port_id, $graph->getPort()?->port_id);
        $this->assertEquals($port->device_id, $graph->getDevice()?->device_id);
    }

    public function test_graph_file_sets_title_and_options(): void
    {
        $device = Device::factory()->create();

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_ib_dns_performance', 'id' => $device->device_id, 'title' => 'yes']));
        $this->assertInstanceOf(RrdGraphInterface::class, $graph);
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

        $this->assertInstanceOf(RrdGraphInterface::class, $graph);
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
}
