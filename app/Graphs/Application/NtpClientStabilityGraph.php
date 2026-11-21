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
                style: new SeriesStyle(fill: true, rollingAverages: true, percentiles: [25, 50, 75])),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: 'PPM', legendTitle: 'PPM', palette: 'rainbow_stats_purple');
    }
}
