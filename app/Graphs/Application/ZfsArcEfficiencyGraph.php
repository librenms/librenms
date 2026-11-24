<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcEfficiencyGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'cache_hits_per' => new DataSeries($metric, 'cache_hits_per', __('graph.application.zfs.cache_hit')),
            'cache_miss_per' => new DataSeries($metric, 'cache_miss_per', __('graph.application.zfs.cache_miss')),
            'actual_hit_per' => new DataSeries($metric, 'actual_hit_per', __('graph.application.zfs.actual_hit')),
            'data_demand_per' => new DataSeries($metric, 'data_demand_per', __('graph.application.zfs.data_demand')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.percent'), palette: 'psychedelic');
    }
}
