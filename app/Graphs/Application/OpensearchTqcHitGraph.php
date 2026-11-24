<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTqcHitGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tqc']);

        return [
            'tqc_hit' => new DataSeries($metric, 'tqc_hit', __('graph.application.opensearch.qc_hits')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.hits'), palette: 'greens');
    }
}
