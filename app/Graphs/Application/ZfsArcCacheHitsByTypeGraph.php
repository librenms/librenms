<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcCacheHitsByTypeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'demand_hits_per' => new DataSeries($metric, 'demand_hits_per', __('graph.application.zfs.demand')),
            'pre_hits_per' => new DataSeries($metric, 'pre_hits_per', __('graph.application.zfs.prefetch')),
            'meta_hits_per' => new DataSeries($metric, 'meta_hits_per', __('graph.application.zfs.demand_meta')),
            'pre_meta_hits_per' => new DataSeries($metric, 'pre_meta_hits_per', __('graph.application.zfs.prefetch_meta')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.percent_of_hits'), palette: 'psychedelic');
    }
}
