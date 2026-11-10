<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Legacy\LegacyGraph;
use App\Graphs\Bill\HistoricbitsGraph;
use App\Graphs\Device\NetstatIpGraph;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Tests\TestCase;

class GraphFactoryTest extends TestCase
{
    public function test_it_resolves_graph_classes(): void
    {
        $factory = new GraphFactory;

        $this->assertInstanceOf(NetstatIpGraph::class, $factory->graphFor(new GraphParameters(['type' => 'device_netstat_ip'])));
        $this->assertInstanceOf(HistoricbitsGraph::class, $factory->graphFor(new GraphParameters(['type' => 'bill_historicbits'])));
    }

    public function test_it_falls_back_to_legacy_graphs(): void
    {
        $this->assertInstanceOf(LegacyGraph::class, (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_poller_perf'])));
    }

    public function test_it_rejects_unknown_graphs(): void
    {
        $this->expectException(InvalidGraph::class);

        (new GraphFactory)->graphFor(new GraphParameters(['type' => 'device_doesnotexist']));
    }

    public function test_it_does_not_resolve_abstract_graph_classes(): void
    {
        $this->expectException(InvalidGraph::class);

        (new GraphFactory)->graphFor(new GraphParameters(['type' => 'bill_bill']));
    }

    public function test_subtypes_may_contain_spaces(): void
    {
        $this->assertInstanceOf(LegacyGraph::class, (new GraphFactory)->graphFor(new GraphParameters(['type' => 'customoid_Test OID'])));
    }

    public function test_only_plain_subtypes_resolve_to_classes(): void
    {
        $factory = new GraphFactory;

        foreach (['device_netstat ip', 'device_Netstat_Ip', 'device_netstat-ip', 'device_netstat.ip'] as $type) {
            try {
                $this->assertNotInstanceOf(NetstatIpGraph::class, $factory->graphFor(new GraphParameters(['type' => $type])), $type);
            } catch (InvalidGraph) {
                $this->addToAssertionCount(1); // not a graph at all is fine too
            }
        }
    }

    public function test_auth_files_and_paths_are_not_graphs(): void
    {
        foreach (['device_auth', 'device_..', 'device_../port/bits', 'customoid_..'] as $type) {
            try {
                (new GraphFactory)->graphFor(new GraphParameters(['type' => $type]));
                $this->fail("$type should not be a graph");
            } catch (InvalidGraph) {
                $this->addToAssertionCount(1);
            }
        }
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
