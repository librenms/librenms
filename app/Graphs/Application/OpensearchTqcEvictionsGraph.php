<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTqcEvictionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tqc']);

        return [
            'tqc_evictions' => new DataSeries($metric, 'tqc_evictions', __('graph.application.opensearch.qc_evictions')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.evictions'), palette: 'greens');
    }
}
