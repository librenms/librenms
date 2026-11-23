<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindCacheTreeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'cache']);

        return [
            'ctmt' => new DataSeries($metric, 'ctmt', __('graph.series.total')),
            'ctmiu' => new DataSeries($metric, 'ctmiu', __('graph.application.bind.in_use')),
            'cthmiu' => new DataSeries($metric, 'cthmiu', __('graph.application.bind.highest_in_use')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.tree_memory'), palette: 'psychedelic');
    }
}
