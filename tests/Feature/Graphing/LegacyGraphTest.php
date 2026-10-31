<?php

namespace LibreNMS\Tests\Feature\Graphing;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\LegacyGraph;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Interfaces\Data\Graphing\RrdGraphInterface;
use LibreNMS\Tests\TestCase;

class LegacyGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_file_resolves_the_port_and_device(): void
    {
        $port = Port::factory()->for(Device::factory())->create();

        $graph = (new GraphFactory)->graphFor('port_bits', ['id' => $port->port_id]);

        $this->assertInstanceOf(LegacyGraph::class, $graph);
        $this->assertTrue($graph->authorize());
        $this->assertEquals($port->port_id, $graph->getPort()?->port_id);
        $this->assertEquals($port->device_id, $graph->getDevice()?->device_id);
    }

    public function test_graph_file_sets_title_and_options(): void
    {
        $device = Device::factory()->create();

        $graph = (new GraphFactory)->graphFor('device_ib_dns_performance', ['id' => $device->device_id, 'title' => 'yes']);
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

        $graph = (new GraphFactory)->graphFor('device_ib_dns_performance', ['id' => $device->device_id, 'graph_title' => 'Custom']);

        $this->assertInstanceOf(RrdGraphInterface::class, $graph);
        $this->assertContains('--title=Custom', $graph->getRrdCommandOptions());
    }
}
