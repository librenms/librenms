<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistQueriesLatencyGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'latency_0_1' => new DataSeries($metric, 'latency_0_1', __('graph.application.powerdns_dnsdist.under_1_ms'), style: new SeriesStyle(fill: true)),
            'latency_1_10' => new DataSeries($metric, 'latency_1_10', __('graph.application.powerdns_dnsdist.1_10_ms'), style: new SeriesStyle(fill: true)),
            'latency_10_50' => new DataSeries($metric, 'latency_10_50', __('graph.application.powerdns_dnsdist.10_50_ms'), style: new SeriesStyle(fill: true)),
            'latency_50_100' => new DataSeries($metric, 'latency_50_100', __('graph.application.powerdns_dnsdist.50_100_ms'), style: new SeriesStyle(fill: true)),
            'latency_100_1000' => new DataSeries($metric, 'latency_100_1000', __('graph.application.powerdns_dnsdist.100_1000_ms'), style: new SeriesStyle(fill: true)),
            'latency_slow' => new DataSeries($metric, 'latency_slow', __('graph.application.powerdns_dnsdist.over_1_sec'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.queries_answer_latency'));
    }
}
