<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2SizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_size' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_size', __('graph.application.zfs.l2_size'),
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
