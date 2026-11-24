<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;

class PowerdnsRecursorAnswersGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $stacked = new SeriesStyle(stacked: true);

        return [
            'answers0_1' => new DataSeries($metric, 'answers0-1', __('graph.application.powerdns_recursor.answers_0_1ms'), style: $stacked),
            'answers1_10' => new DataSeries($metric, 'answers1-10', __('graph.application.powerdns_recursor.answers_1_10ms'), style: $stacked),
            'answers10_100' => new DataSeries($metric, 'answers10-100', __('graph.application.powerdns_recursor.answers_10_100ms'), style: $stacked),
            'answers100_1000' => new DataSeries($metric, 'answers100-1000', __('graph.application.powerdns_recursor.answers_100_1000ms'), style: $stacked),
            'answers_slow' => new DataSeries($metric, 'answers-slow', __('graph.application.powerdns_recursor.answers_slow'), style: $stacked),
            'total' => new SeriesSum(['answers0_1', 'answers1_10', 'answers10_100', 'answers100_1000', 'answers_slow'], __('graph.series.total'),
                style: new SeriesStyle(legendOnly: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.powerdns_recursor.answers_per_sec'), palette: 'oranges');
    }
}
