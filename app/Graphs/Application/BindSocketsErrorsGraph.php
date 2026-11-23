<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsErrorsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4se' => new DataSeries($metric, 'ui4se', __('graph.application.bind.udp_ipv4_send')),
            'ui6se' => new DataSeries($metric, 'ui6se', __('graph.application.bind.udp_ipv6_send')),
            'ti4se' => new DataSeries($metric, 'ti4se', __('graph.application.bind.tcp_ipv4_send')),
            'ti6se' => new DataSeries($metric, 'ti6se', __('graph.application.bind.tcp_ipv6_send')),
            'ui4re' => new DataSeries($metric, 'ui4re', __('graph.application.bind.udp_ipv4_recv')),
            'ui6re' => new DataSeries($metric, 'ui6re', __('graph.application.bind.udp_ipv6_recv')),
            'ti4re' => new DataSeries($metric, 'ti4re', __('graph.application.bind.tcp_ipv4_recv')),
            'ti6re' => new DataSeries($metric, 'ti6re', __('graph.application.bind.tcp_ipv6_recv')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.errors_per_second'), palette: 'psychedelic');
    }
}
