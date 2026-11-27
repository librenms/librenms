<?php

namespace App\Graphs\Port;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;

/**
 * Packets per second carried on an ATM virtual path
 */
class AtmvpPacketsGraph extends AtmvpGraph
{
    public function getSeries(): array
    {
        return $this->duplex('inpackets', 'outpackets', 'pps', SeriesRole::PacketsIn, SeriesRole::PacketsOut);
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.packets'));
    }
}
