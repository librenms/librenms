<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistQueriesStatsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'queries_count' => new DataSeries($metric, 'queries_count', __('graph.series.total'), style: new SeriesStyle(fill: true)),
            'queries_recursive' => new DataSeries($metric, 'queries_recursive', __('graph.application.powerdns_dnsdist.recursive'), style: new SeriesStyle(fill: true)),
            'queries_empty' => new DataSeries($metric, 'queries_empty', __('graph.application.powerdns_dnsdist.empty'), style: new SeriesStyle(fill: true)),
            'queries_self_answer' => new DataSeries($metric, 'queries_self_answer', __('graph.application.powerdns_dnsdist.self_answer'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.queries_stats'));
    }
}
