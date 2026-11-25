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
            'jitter' => new DataSeries($metric, 'jitter', __('graph.application.ntp_client.jitter'), 's', 0.001),
            'noise' => new DataSeries($metric, 'noise', __('graph.application.ntp_client.noise'), 's', 0.001),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: __('graph.units.seconds'), legendTitle: __('graph.units.seconds'));
    }
}
