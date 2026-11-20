<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class NtpClientStatsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        // stored in milliseconds
        return [
            'jitter' => new DataSeries($metric, 'jitter', __('graphs.ntp-client.jitter'), 's', 0.001),
            'noise' => new DataSeries($metric, 'noise', __('graphs.ntp-client.noise'), 's', 0.001),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: __('graphs.units.seconds'), legendTitle: __('graphs.units.seconds'));
    }
}
