<?php

namespace App\Graphs\Port;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;

/**
 * ATM cells per second on a virtual path
 */
class AtmvpCellsGraph extends AtmvpGraph
{
    public function getSeries(): array
    {
        return $this->duplex('incells', 'outcells', '', SeriesRole::PacketsIn, SeriesRole::PacketsOut);
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.cells'));
    }
}
