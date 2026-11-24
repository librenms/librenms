<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsSuTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ts']);

        return [
            'ts_su_time' => new DataSeries($metric, 'ts_su_time', __('graph.application.opensearch.suggest_time')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds_per_second'), palette: 'greens');
    }
}
