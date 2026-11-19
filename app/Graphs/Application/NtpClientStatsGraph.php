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
            'jitter' => new DataSeries($metric, 'jitter', 'Jitter', 's', 0.001),
            'noise' => new DataSeries($metric, 'noise', 'Noise', 's', 0.001),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(verticalLabel: 'Seconds', legendTitle: 'Seconds');
    }
}
