<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindResolverNafGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'resolver']);

        return [
            'i4naf' => new DataSeries($metric, 'i4naf', __('graph.application.bind.ipv4_ns_ftchd')),
            'i6naf' => new DataSeries($metric, 'i6naf', __('graph.application.bind.ipv6_ns_ftchd')),
            'i4naff' => new DataSeries($metric, 'i4naff', __('graph.application.bind.ipv4_failed')),
            'i6naff' => new DataSeries($metric, 'i6naff', __('graph.application.bind.ipv6_failed')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.queries_per_second'), palette: 'psychedelic');
    }
}
