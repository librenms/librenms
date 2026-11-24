<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsOpenedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4so' => new DataSeries($metric, 'ui4so', __('graph.application.bind.udp_ipv4')),
            'ui6so' => new DataSeries($metric, 'ui6so', __('graph.application.bind.udp_ipv6')),
            'ti4so' => new DataSeries($metric, 'ti4so', __('graph.application.bind.tcp_ipv4')),
            'ti6so' => new DataSeries($metric, 'ti6so', __('graph.application.bind.tcp_ipv6')),
            'rso' => new DataSeries($metric, 'rso', __('graph.application.bind.raw')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.opened_sec'), palette: 'psychedelic');
    }
}
