<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'arc_size' => new DataSeries($metric, 'arc_size', __('graph.application.zfs.arc_size')),
            'target_size' => new DataSeries($metric, 'target_size', __('graph.application.zfs.target_size')),
            'target_size_max' => new DataSeries($metric, 'target_size_max', __('graph.application.zfs.target_max')),
            'target_size_min' => new DataSeries($metric, 'target_size_min', __('graph.application.zfs.target_min')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
