<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsRecursorCachePerformanceGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $area = new SeriesStyle(fill: true);

        return [
            'cache_hits' => new DataSeries($metric, 'cache-hits', __('graph.application.powerdns_recursor.query_cache_hits'), style: $area),
            'cache_misses' => new DataSeries($metric, 'cache-misses', __('graph.application.powerdns_recursor.query_cache_misses'), style: $area),
            'packetcache_hits' => new DataSeries($metric, 'packetcache-hits', __('graph.application.powerdns_recursor.packet_cache_hits'), style: $area),
            'packetcache_misses' => new DataSeries($metric, 'packetcache-misses', __('graph.application.powerdns_recursor.packet_cache_misses'), style: $area),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.packets_per_second'), palette: 'mixed');
    }
}
