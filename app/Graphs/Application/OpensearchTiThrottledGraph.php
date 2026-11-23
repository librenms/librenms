<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTiThrottledGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ti']);

        return [
            'ti_throttled' => new DataSeries($metric, 'ti_throttled', __('graph.application.opensearch.throttled')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.throttled'), palette: 'greens');
    }
}
