<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcCacheMissGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'demand_data_misses' => new DataSeries($metric, 'demand_data_misses', __('graph.application.zfs.demand')),
            'demand_meta_misses' => new DataSeries($metric, 'demand_meta_misses', __('graph.application.zfs.demand_meta')),
            'pre_data_misses' => new DataSeries($metric, 'pre_data_misses', __('graph.application.zfs.prefetch')),
            'pre_meta_misses' => new DataSeries($metric, 'pre_meta_misses', __('graph.application.zfs.prefetch_meta')),
            'arc_misses' => new DataSeries($metric, 'arc_misses', __('graph.application.zfs.arc')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.misses_second'), palette: 'psychedelic');
    }
}
