<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistRulesStatsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'rule_drop' => new DataSeries($metric, 'rule_drop', __('graph.application.powerdns_dnsdist.drop'), style: new SeriesStyle(fill: true)),
            'rule_nxdomain' => new DataSeries($metric, 'rule_nxdomain', __('graph.application.powerdns_dnsdist.nxdomain'), style: new SeriesStyle(fill: true)),
            'rule_refused' => new DataSeries($metric, 'rule_refused', __('graph.application.powerdns_dnsdist.refused'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.rule_stats'));
    }
}
