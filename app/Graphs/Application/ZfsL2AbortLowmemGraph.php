<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2AbortLowmemGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_abort_lowmem' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_abort_lowmem', __('graph.application.zfs.l2_abort_lowmem'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.zfs.instances_second'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
