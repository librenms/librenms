<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyBytesToClientGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'bytes_to_client' => new DataSeries($this->metric(), 'bytes_to_client', __('graph.application.privoxy.bytes_to_client'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.bytes_to_client'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
