<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NtpClientOffsetGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        // stored in milliseconds
        return [
            'offset' => new DataSeries($this->metric(), 'offset', __('graph.application.ntp_client.offset'), 's', 0.001,
                style: new SeriesStyle(fill: true, rollingAverages: true, percentiles: [25, 50, 75])),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: __('graph.units.seconds'), legendTitle: __('graph.units.seconds'), palette: 'rainbow_stats_purple');
    }
}
