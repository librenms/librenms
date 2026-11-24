<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcMiscGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'deleted' => new DataSeries($metric, 'deleted', __('graph.application.zfs.deleted')),
            'recycle_miss' => new DataSeries($metric, 'recycle_miss', __('graph.application.zfs.recycle_misses')),
            'evict_skip' => new DataSeries($metric, 'evict_skip', __('graph.application.zfs.evict_skip')),
            'mutex_skip' => new DataSeries($metric, 'mutex_skip', __('graph.application.zfs.mutex_skip')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
