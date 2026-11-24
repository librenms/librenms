<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistQueriesDropGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'queries_drop_no_policy' => new DataSeries($metric, 'queries_drop_no_policy', __('graph.application.powerdns_dnsdist.no_server'), style: new SeriesStyle(fill: true)),
            'queries_drop_nc' => new DataSeries($metric, 'queries_drop_nc', __('graph.application.powerdns_dnsdist.non_compliant'), style: new SeriesStyle(fill: true)),
            'queries_drop_nc_answer' => new DataSeries($metric, 'queries_drop_nc_answer', __('graph.application.powerdns_dnsdist.non_compliant_answer'), style: new SeriesStyle(fill: true)),
            'queries_acl_drop' => new DataSeries($metric, 'queries_acl_drop', __('graph.application.powerdns_dnsdist.acl'), style: new SeriesStyle(fill: true)),
            'queries_failure' => new DataSeries($metric, 'queries_failure', __('graph.application.powerdns_dnsdist.failure'), style: new SeriesStyle(fill: true)),
            'queries_serv_fail' => new DataSeries($metric, 'queries_serv_fail', __('graph.application.powerdns_dnsdist.servfail'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.queries_drop'));
    }
}
