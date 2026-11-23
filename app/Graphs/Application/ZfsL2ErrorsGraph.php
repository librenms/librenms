<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2ErrorsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_errors' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_errors', __('graph.application.zfs.l2_errors'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.errors_per_second'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
