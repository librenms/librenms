<?php

namespace App\Graphs\Port;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;

/**
 * Traffic of the packets carried on an ATM virtual path
 */
class AtmvpBitsGraph extends AtmvpGraph
{
    public function getSeries(): array
    {
        return $this->duplex('inpacketoctets', 'outpacketoctets', 'bps', SeriesRole::In, SeriesRole::Out, scale: 8, percentileLines: true);
    }

    protected function options(): GraphOptions
    {
        return GraphOptions::forTraffic();
    }
}
