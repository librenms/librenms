<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\StatsOptions;

class NtpClientOffsetGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        // stored in milliseconds
        return [
            'offset' => new DataSeries($this->metric(), 'offset', __('graphs.ntp-client.offset'), 's', 0.001, style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: __('graphs.units.seconds'), legendTitle: __('graphs.units.seconds'), palette: 'rainbow_stats_purple', stats: new StatsOptions);
    }
}
