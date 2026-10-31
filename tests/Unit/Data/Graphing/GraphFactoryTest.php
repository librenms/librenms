<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\LegacyGraph;
use App\Graphs\Bill\HistoricbitsGraph;
use App\Graphs\Device\NetstatIpGraph;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Tests\TestCase;

class GraphFactoryTest extends TestCase
{
    public function test_it_resolves_graph_classes(): void
    {
        $factory = new GraphFactory;

        $this->assertInstanceOf(NetstatIpGraph::class, $factory->graphFor('device_netstat_ip'));
        $this->assertInstanceOf(HistoricbitsGraph::class, $factory->graphFor('bill_historicbits'));
    }

    public function test_it_falls_back_to_legacy_graphs(): void
    {
        $this->assertInstanceOf(LegacyGraph::class, (new GraphFactory)->graphFor('device_poller_perf'));
    }

    public function test_it_rejects_unknown_graphs(): void
    {
        $this->expectException(InvalidGraph::class);

        (new GraphFactory)->graphFor('device_doesnotexist');
    }

    public function test_it_does_not_resolve_abstract_graph_classes(): void
    {
        $this->expectException(InvalidGraph::class);

        (new GraphFactory)->graphFor('bill_bill');
    }

    public function test_it_lists_subtypes_from_classes_and_legacy_files(): void
    {
        $factory = new GraphFactory;

        $device = $factory->subtypes('device');
        $this->assertContains('processor', $device);
        $this->assertContains('netstat_ip', $device);
        $this->assertContains('poller_perf', $device);
        $this->assertNotContains('auth', $device);

        $bill = $factory->subtypes('bill');
        $this->assertContains('historicbits', $bill);
        $this->assertContains('bits', $bill);
        $this->assertNotContains('bill', $bill);
    }
}
