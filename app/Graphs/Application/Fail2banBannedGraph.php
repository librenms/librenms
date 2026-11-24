<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class Fail2banBannedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'banned' => new DataSeries($this->metric(), 'banned', __('graph.application.fail2ban.banned'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.fail2ban.banned_ips'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
