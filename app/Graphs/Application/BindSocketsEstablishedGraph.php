<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsEstablishedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4ce' => new DataSeries($metric, 'ui4ce', __('graph.application.bind.udp_ipv4')),
            'ui6ce' => new DataSeries($metric, 'ui6ce', __('graph.application.bind.udp_ipv6')),
            'ti4ce' => new DataSeries($metric, 'ti4ce', __('graph.application.bind.tcp_ipv4')),
            'ti6ce' => new DataSeries($metric, 'ti6ce', __('graph.application.bind.tcp_ipv6')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.establish_sec'), palette: 'psychedelic');
    }
}
