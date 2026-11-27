<?php

namespace App\Graphs\Port;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;

/**
 * Packet errors per second on an ATM virtual path
 */
class AtmvpErrorsGraph extends AtmvpGraph
{
    public function getSeries(): array
    {
        return $this->duplex('inpacketerrors', 'outpacketerrors', 'pps', SeriesRole::Error, SeriesRole::Error);
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.errors'));
    }
}
