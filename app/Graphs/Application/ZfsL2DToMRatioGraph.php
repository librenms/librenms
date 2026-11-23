<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ZfsL2DToMRatioGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'l2_d_to_m_ratio' => new DataSeries($this->metric(['stat' => '_____group2']), 'l2_d_to_m_ratio', __('graph.application.zfs.l2_data_to_meta'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.ratio'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
