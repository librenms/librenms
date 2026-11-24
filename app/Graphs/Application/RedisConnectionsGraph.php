<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisConnectionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'connections']);

        return [
            'received' => new DataSeries($metric, 'received', __('graph.series.received')),
            'rejected' => new DataSeries($metric, 'rejected', __('graph.series.rejected')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.connections'), palette: 'mixed');
    }
}
