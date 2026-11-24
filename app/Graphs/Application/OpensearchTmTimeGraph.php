<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTmTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tm']);

        return [
            'tm_time' => new DataSeries($metric, 'tm_time', __('graph.application.opensearch.merges_time')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds_per_second'), palette: 'greens');
    }
}
