<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyVer2Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'ver_2' => new DataSeries($this->metric(), 'ver_2', __('graph.application.privoxy.ver_2'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.ver_2'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
