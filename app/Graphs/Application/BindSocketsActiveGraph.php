<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsActiveGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4sa' => new DataSeries($metric, 'ui4sa', __('graph.application.bind.udp_ipv4')),
            'ui6sa' => new DataSeries($metric, 'ui6sa', __('graph.application.bind.udp_ipv6')),
            'ti6sa' => new DataSeries($metric, 'ti6sa', __('graph.application.bind.tcp_ipv6')),
            'rsa' => new DataSeries($metric, 'rsa', __('graph.application.bind.raw')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.active_sockets'), palette: 'psychedelic');
    }
}
