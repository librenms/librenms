<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsRecursorQuestionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $area = new SeriesStyle(fill: true);

        return [
            'questions' => new DataSeries($metric, 'questions', __('graph.application.powerdns_recursor.total_questions'), style: $area),
            'ipv6_questions' => new DataSeries($metric, 'ipv6-questions', __('graph.application.powerdns_recursor.ipv6_questions'), style: $area),
            'tcp_questions' => new DataSeries($metric, 'tcp-questions', __('graph.application.powerdns_recursor.tcp_questions'), style: $area),
            'over_capacity_drops' => new DataSeries($metric, 'over-capacity-drops', __('graph.application.powerdns_recursor.over_capacity_drops'), style: $area),
            'policy_drops' => new DataSeries($metric, 'policy-drops', __('graph.application.powerdns_recursor.policy_drops'), style: $area),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.powerdns_recursor.questions_per_sec'), palette: 'mixed');
    }
}
