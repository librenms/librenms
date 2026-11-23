<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyUniqueBdomainsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'unique_bdomains' => new DataSeries($this->metric(), 'unique_bdomains', __('graph.application.privoxy.unique_bdomains'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.unique_bdomains'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
