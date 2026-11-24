<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsClosedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4sc' => new DataSeries($metric, 'ui4sc', __('graph.application.bind.udp_ipv4')),
            'ui6sc' => new DataSeries($metric, 'ui6sc', __('graph.application.bind.udp_ipv6')),
            'ti4sc' => new DataSeries($metric, 'ti4sc', __('graph.application.bind.tcp_ipv4')),
            'ti6sc' => new DataSeries($metric, 'ti6sc', __('graph.application.bind.tcp_ipv6')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.closed_sec'), palette: 'psychedelic');
    }
}
