<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyVer3Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'ver_3' => new DataSeries($this->metric(), 'ver_3', __('graph.application.privoxy.ver_3'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.ver_3'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
