<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTiThrottledTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ti']);

        return [
            'ti_throttled_time' => new DataSeries($metric, 'ti_throttled_time', __('graph.application.opensearch.throttled_time')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds_per_second'), palette: 'greens');
    }
}
