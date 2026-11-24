<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindCacheHeapGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'cache']);

        return [
            'chmt' => new DataSeries($metric, 'chmt', __('graph.series.total')),
            'chmiu' => new DataSeries($metric, 'chmiu', __('graph.application.bind.in_use')),
            'chhmiu' => new DataSeries($metric, 'chhmiu', __('graph.application.bind.highest_in_use')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.heap_memory'), palette: 'psychedelic');
    }
}
