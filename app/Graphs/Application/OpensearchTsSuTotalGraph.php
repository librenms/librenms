<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsSuTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ts']);

        return [
            'ts_su_total' => new DataSeries($metric, 'ts_su_total', __('graph.application.opensearch.suggests')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.suggests_per_second'), palette: 'greens');
    }
}
