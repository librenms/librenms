<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsArcSizeBreakdownGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'mfu_size' => new DataSeries($metric, 'mfu_size', __('graph.application.zfs.frequently_used')),
            'p' => new DataSeries($metric, 'p', __('graph.application.zfs.recently_used')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
