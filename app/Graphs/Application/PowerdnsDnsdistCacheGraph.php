<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistCacheGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'cache_hits' => new DataSeries($metric, 'cache_hits', __('graph.application.powerdns_dnsdist.hits'), style: new SeriesStyle(fill: true)),
            'cache_miss' => new DataSeries($metric, 'cache_miss', __('graph.application.powerdns_dnsdist.miss'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.cache'));
    }
}
