<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyUbdNpPerGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'ubd_np_per' => new DataSeries($this->metric(), 'ubd_np_per', __('graph.application.privoxy.ubd_np_per'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.ubd_np_per'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
