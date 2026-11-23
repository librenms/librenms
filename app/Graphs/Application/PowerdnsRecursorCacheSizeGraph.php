<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PowerdnsRecursorCacheSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'cache_entries' => new DataSeries($metric, 'cache-entries', __('graph.application.powerdns_recursor.query_cache')),
            'packetcache_entries' => new DataSeries($metric, 'packetcache-entries', __('graph.application.powerdns_recursor.packet_cache')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.entries'), palette: 'purples');
    }
}
