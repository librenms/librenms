<?php

namespace LibreNMS\Tests\Feature\Graphing;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Graph;

class GraphSubtypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_graphs_are_limited_to_the_requested_type(): void
    {
        LibrenmsConfig::set('graph_types.device.test_device_graph', ['section' => 'test']);
        LibrenmsConfig::set('graph_types.port.test_port_graph', ['section' => 'test']);
        LibrenmsConfig::set('graph_types.device.test_unused_graph', ['section' => 'test']);

        $device = Device::factory()->create();
        $device->graphs()->createMany([['graph' => 'test_device_graph'], ['graph' => 'test_port_graph']]);

        $deviceTypes = Graph::getSubtypes('device', $device);
        $this->assertContains('test_device_graph', $deviceTypes);
        $this->assertNotContains('test_port_graph', $deviceTypes);
        $this->assertNotContains('test_unused_graph', $deviceTypes);

        $portTypes = Graph::getSubtypes('port', $device);
        $this->assertContains('test_port_graph', $portTypes);
        $this->assertNotContains('test_device_graph', $portTypes);
    }

    public function test_type_without_configured_graphs_only_lists_its_own_subtypes(): void
    {
        LibrenmsConfig::set('graph_types.device.test_device_graph', ['section' => 'test']);

        $device = Device::factory()->create();
        $device->graphs()->create(['graph' => 'test_device_graph']);

        $this->assertNotContains('test_device_graph', Graph::getSubtypes('nosuchtype', $device));
    }
}
