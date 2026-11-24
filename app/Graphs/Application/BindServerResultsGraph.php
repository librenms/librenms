<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindServerResultsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'server']);

        return [
            'qrisa' => new DataSeries($metric, 'qrisa', __('graph.application.bind.successful')),
            'qriaa' => new DataSeries($metric, 'qriaa', __('graph.application.bind.authoritative')),
            'qrinaa' => new DataSeries($metric, 'qrinaa', __('graph.application.bind.non_authoritative')),
            'qrin' => new DataSeries($metric, 'qrin', __('graph.application.bind.nx_rr_set')),
            'qris' => new DataSeries($metric, 'qris', __('graph.application.bind.servfail')),
            'qrind' => new DataSeries($metric, 'qrind', __('graph.application.bind.nxdomain')),
            'rwes' => new DataSeries($metric, 'rwes', __('graph.application.bind.with_edns_0')),
            'qcr' => new DataSeries($metric, 'qcr', __('graph.application.bind.caused_rec')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
