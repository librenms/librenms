<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NtpClientFreqGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'frequency' => new DataSeries($this->metric(), 'frequency', __('graph.application.ntp_client.frequency'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentiles: [25, 50, 75])),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: 'PPM', legendTitle: 'PPM', palette: 'rainbow_stats_purple');
    }
}
