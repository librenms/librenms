<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcCacheHitsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'demand_data_hits' => new DataSeries($metric, 'demand_data_hits', __('graph.application.zfs.demand')),
            'demand_meta_hits' => new DataSeries($metric, 'demand_meta_hits', __('graph.application.zfs.demand_meta')),
            'mru_hits' => new DataSeries($metric, 'mru_hits', __('graph.application.zfs.mru')),
            'mru_ghost_hits' => new DataSeries($metric, 'mru_ghost_hits', __('graph.application.zfs.mru_ghost')),
            'mfu_hits' => new DataSeries($metric, 'mfu_hits', __('graph.application.zfs.mfu')),
            'mfu_ghost_hits' => new DataSeries($metric, 'mfu_ghost_hits', __('graph.application.zfs.mfu_ghost')),
            'pre_data_hits' => new DataSeries($metric, 'pre_data_hits', __('graph.application.zfs.prefetch')),
            'pre_meta_hits' => new DataSeries($metric, 'pre_meta_hits', __('graph.application.zfs.prefetch_meta')),
            'anon_hits' => new DataSeries($metric, 'anon_hits', __('graph.application.zfs.anonymous')),
            'arc_hits' => new DataSeries($metric, 'arc_hits', __('graph.application.zfs.arc')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.hits_second'), palette: 'psychedelic');
    }
}
