<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcCacheMissesByTypeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'demand_misses_per' => new DataSeries($metric, 'demand_misses_per', __('graph.application.zfs.demand')),
            'pre_misses_per' => new DataSeries($metric, 'pre_misses_per', __('graph.application.zfs.prefetch')),
            'meta_misses_per' => new DataSeries($metric, 'meta_misses_per', __('graph.application.zfs.demand_meta')),
            'pre_meta_misses_per' => new DataSeries($metric, 'pre_meta_misses_per', __('graph.application.zfs.prefetch_meta')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.percent_of_misses'), palette: 'psychedelic');
    }
}
