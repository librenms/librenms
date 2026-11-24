<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcCacheHitsByListGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'anon_hits_per' => new DataSeries($metric, 'anon_hits_per', __('graph.application.zfs.anon_used')),
            'mru_per' => new DataSeries($metric, 'mru_per', __('graph.application.zfs.most_recent')),
            'mfu_per' => new DataSeries($metric, 'mfu_per', __('graph.application.zfs.most_frequent')),
            'mru_ghost_per' => new DataSeries($metric, 'mru_ghost_per', __('graph.application.zfs.mru_ghost')),
            'mfu_ghost_per' => new DataSeries($metric, 'mfu_ghost_per', __('graph.application.zfs.mfu_ghost')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.percent_of_hits'), palette: 'psychedelic');
    }
}
