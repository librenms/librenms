<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NtpClientStabilityGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'stability' => new DataSeries($this->metric(), 'stability', __('graph.application.ntp_client.stability'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            verticalLabel: 'PPM',
            legendTitle: 'PPM',
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
