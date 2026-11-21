<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\StatsOptions;

class NtpClientFreqGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'frequency' => new DataSeries($this->metric(), 'frequency', __('graph.application.ntp_client.frequency'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: 'PPM', legendTitle: 'PPM', palette: 'rainbow_stats_purple', stats: new StatsOptions);
    }
}
