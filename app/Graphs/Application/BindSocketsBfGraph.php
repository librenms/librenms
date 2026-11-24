<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsBfGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4sbf' => new DataSeries($metric, 'ui4sbf', __('graph.application.bind.udp_ipv4')),
            'ui6sbf' => new DataSeries($metric, 'ui6sbf', __('graph.application.bind.udp_ipv6')),
            'ti4sbf' => new DataSeries($metric, 'ti4sbf', __('graph.application.bind.tcp_ipv4')),
            'ti6sbf' => new DataSeries($metric, 'ti6sbf', __('graph.application.bind.tcp_ipv6')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.failures_per_second'), palette: 'psychedelic');
    }
}
