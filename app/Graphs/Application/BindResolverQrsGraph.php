<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindResolverQrsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'resolver']);

        return [
            'i4qs' => new DataSeries($metric, 'i4qs', __('graph.application.bind.ipv4_sent')),
            'i6qs' => new DataSeries($metric, 'i6qs', __('graph.application.bind.ipv6_sent')),
            'i4rr' => new DataSeries($metric, 'i4rr', __('graph.application.bind.ipv4_rcvd')),
            'i6rr' => new DataSeries($metric, 'i6rr', __('graph.application.bind.ipv6_rcvd')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.queries_per_second'), palette: 'psychedelic');
    }
}
