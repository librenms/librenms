<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTqcTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tqc']);

        return [
            'tqc_total' => new DataSeries($metric, 'tqc_total', __('graph.application.opensearch.queries')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.cached_queries'), palette: 'greens');
    }
}
