<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2MfuAsizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_mfu_asize' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_mfu_asize', __('graph.application.zfs.l2_mfu_asize'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.bytes'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
