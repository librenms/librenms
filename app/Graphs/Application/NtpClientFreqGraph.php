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
