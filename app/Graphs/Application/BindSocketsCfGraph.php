<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindSocketsCfGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sockets']);

        return [
            'ui4scf' => new DataSeries($metric, 'ui4scf', __('graph.application.bind.udp_ipv4')),
            'ui6scf' => new DataSeries($metric, 'ui6scf', __('graph.application.bind.udp_ipv6')),
            'ti4scf' => new DataSeries($metric, 'ti4scf', __('graph.application.bind.tcp_ipv4')),
            'ti6scf' => new DataSeries($metric, 'ti6scf', __('graph.application.bind.tcp_ipv6')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.failures_per_second'), palette: 'psychedelic');
    }
}
