<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTrExtTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tr']);

        return [
            'tr_ext_total' => new DataSeries($metric, 'tr_ext_total', __('graph.application.opensearch.ext_refreshes')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops_per_second'), palette: 'greens');
    }
}
