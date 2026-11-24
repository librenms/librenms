<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * Total connections of every monitored port, for the Apps overview page
 */
class PortactivityPortsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $ports = $this->application()->data['ports'] ?? [];
        sort($ports);
        $series = [];
        foreach ($ports as $port) {
            $series["port_$port"] = new DataSeries($this->metric(['port' => $port]), 'total_conns', (string) $port);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.connections'), palette: 'psychedelic');
    }
}
