<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsScTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ts']);

        return [
            'ts_sc_total' => new DataSeries($metric, 'ts_sc_total', __('graph.application.opensearch.scrolls')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.scrolls_per_second'), palette: 'greens');
    }
}
