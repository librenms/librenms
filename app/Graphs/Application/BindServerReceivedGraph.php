<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindServerReceivedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'server']);

        return [
            'i4rr' => new DataSeries($metric, 'i4rr', __('graph.application.bind.ipv4_req')),
            'i6rr' => new DataSeries($metric, 'i6rr', __('graph.application.bind.ipv6_req')),
            'trr' => new DataSeries($metric, 'trr', __('graph.application.bind.tcp_req')),
            'tqr' => new DataSeries($metric, 'tqr', __('graph.application.bind.tcp_qry')),
            'uqr' => new DataSeries($metric, 'uqr', __('graph.application.bind.udp_qry')),
            'rwer' => new DataSeries($metric, 'rwer', __('graph.application.bind.with_edns_0')),
            'oeor' => new DataSeries($metric, 'oeor', __('graph.application.bind.other_edns')),
            'dqr' => new DataSeries($metric, 'dqr', __('graph.application.bind.dup_qry')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
