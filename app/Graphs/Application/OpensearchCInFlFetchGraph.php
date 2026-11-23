<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCInFlFetchGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_in_fl_fetch' => new DataSeries($metric, 'c_in_fl_fetch', __('graph.application.opensearch.in_fl_fetches')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.fetches'), palette: 'greens');
    }
}
