<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTrTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tr']);

        return [
            'tr_time' => new DataSeries($metric, 'tr_time', __('graph.application.opensearch.index_time')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds_per_second'), palette: 'greens');
    }
}
