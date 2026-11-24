<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcSizePerGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'arc_size_per' => new DataSeries($metric, 'arc_size_per', __('graph.application.zfs.arc_size_percent')),
            'target_size_per' => new DataSeries($metric, 'target_size_per', __('graph.application.zfs.target_size_percent')),
            'min_size_per' => new DataSeries($metric, 'min_size_per', __('graph.application.zfs.target_min_percent')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.zfs.percent_of_max'), palette: 'psychedelic');
    }
}
