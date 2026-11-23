<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyCrunchesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'crunches' => new DataSeries($this->metric(), 'crunches', __('graph.application.privoxy.crunches'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.crunches'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
