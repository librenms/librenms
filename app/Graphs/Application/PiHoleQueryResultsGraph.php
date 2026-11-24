<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PiHoleQueryResultsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'dns_query' => new DataSeries($metric, 'dns_query', __('graph.application.pi_hole.today_dns_queries'), style: new SeriesStyle(fill: true)),
            'ads_blocked' => new DataSeries($metric, 'ads_blocked', __('graph.application.pi_hole.ads_blocked'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.pi_hole.stats'));
    }
}
