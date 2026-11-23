<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyBlockPercentGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'block_percent' => new DataSeries($this->metric(), 'block_percent', __('graph.application.privoxy.block_percent'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.block_percent'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
