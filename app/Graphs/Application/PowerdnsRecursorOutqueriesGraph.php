<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsRecursorOutqueriesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $area = new SeriesStyle(fill: true);

        return [
            'all_outqueries' => new DataSeries($metric, 'all-outqueries', __('graph.series.total'), style: $area),
            'ipv6_outqueries' => new DataSeries($metric, 'ipv6-outqueries', __('graph.application.powerdns_recursor.ipv6'), style: $area),
            'tcp_outqueries' => new DataSeries($metric, 'tcp-outqueries', __('graph.application.powerdns_recursor.tcp'), style: $area),
            'throttled_out' => new DataSeries($metric, 'throttled-out', __('graph.application.powerdns_recursor.throttled'), style: $area),
            'outgoing_timeouts' => new DataSeries($metric, 'outgoing-timeouts', __('graph.application.powerdns_recursor.timeouts'), style: $area),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.queries_per_second'), palette: 'mixed');
    }
}
