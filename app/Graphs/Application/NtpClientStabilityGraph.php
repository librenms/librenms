<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\StatsOptions;

class NtpClientStabilityGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'stability' => new DataSeries($this->metric(), 'stability', __('graph.application.ntp_client.stability'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: 'PPM', legendTitle: 'PPM', palette: 'rainbow_stats_purple', stats: new StatsOptions);
    }
}
