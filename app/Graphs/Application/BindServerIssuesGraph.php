<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindServerIssuesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'server']);

        return [
            'aqr' => new DataSeries($metric, 'aqr', __('graph.application.bind.auth_qry_rej')),
            'rqr' => new DataSeries($metric, 'rqr', __('graph.application.bind.rec_qry_rej')),
            'trs' => new DataSeries($metric, 'trs', __('graph.application.bind.trnctd_rsp_snt')),
            'oqf' => new DataSeries($metric, 'oqf', __('graph.application.bind.oth_qry_fail')),
            'qd' => new DataSeries($metric, 'qd', __('graph.application.bind.qry_dropped')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
