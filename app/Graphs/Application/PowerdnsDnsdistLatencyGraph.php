<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistLatencyGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'latency_100' => new DataSeries($metric, 'latency_100', __('graph.application.powerdns_dnsdist.last_100_pkts'), style: new SeriesStyle(fill: true)),
            'latency_1000' => new DataSeries($metric, 'latency_1000', __('graph.application.powerdns_dnsdist.last_1000_pkts'), style: new SeriesStyle(fill: true)),
            'latency_10000' => new DataSeries($metric, 'latency_10000', __('graph.application.powerdns_dnsdist.last_10000_pkts'), style: new SeriesStyle(fill: true)),
            'latency_1000000' => new DataSeries($metric, 'latency_1000000', __('graph.application.powerdns_dnsdist.last_1000000_pkts'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.average_latency'));
    }
}
