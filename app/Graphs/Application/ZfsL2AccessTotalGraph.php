<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2AccessTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_access_total' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_access_total', __('graph.application.zfs.l2_accesses'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.zfs.accesses_second'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
