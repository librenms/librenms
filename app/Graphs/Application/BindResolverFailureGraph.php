<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindResolverFailureGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'resolver']);

        return [
            'nr' => new DataSeries($metric, 'nr', __('graph.application.bind.nxdomain')),
            'sr' => new DataSeries($metric, 'sr', __('graph.application.bind.servfail')),
            'fr' => new DataSeries($metric, 'fr', __('graph.application.bind.formerr')),
            'rr' => new DataSeries($metric, 'rr', __('graph.application.bind.refused')),
            'eqf' => new DataSeries($metric, 'eqf', __('graph.application.bind.edns_0_qry_fl')),
            'trr' => new DataSeries($metric, 'trr', __('graph.application.bind.trnctd_rcvd')),
            'qr' => new DataSeries($metric, 'qr', __('graph.application.bind.retry')),
            'qt' => new DataSeries($metric, 'qt', __('graph.application.bind.timeout')),
            'ldr' => new DataSeries($metric, 'ldr', __('graph.application.bind.lame_dele')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
