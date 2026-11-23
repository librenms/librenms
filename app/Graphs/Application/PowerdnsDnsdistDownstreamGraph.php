<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistDownstreamGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'downstream_timeout' => new DataSeries($metric, 'downstream_timeout', __('graph.application.powerdns_dnsdist.timeout'), style: new SeriesStyle(fill: true)),
            'downstream_err' => new DataSeries($metric, 'downstream_err', __('graph.series.errors'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.downstream_servers'));
    }
}
