<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTfTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tf']);

        return [
            'tf_total' => new DataSeries($metric, 'tf_total', __('graph.application.opensearch.flush')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops_per_second'), palette: 'greens');
    }
}
