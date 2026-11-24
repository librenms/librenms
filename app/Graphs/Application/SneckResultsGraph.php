<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SneckResultsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'ok' => new DataSeries($metric, 'ok', __('graph.application.sneck.ok')),
            'warning' => new DataSeries($metric, 'warning', __('graph.application.sneck.warning')),
            'critical' => new DataSeries($metric, 'critical', __('graph.application.sneck.critical')),
            'unknown' => new DataSeries($metric, 'unknown', __('graph.application.sneck.unknown')),
            'errored' => new DataSeries($metric, 'errored', __('graph.application.sneck.errored')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.sneck.results'), palette: 'psychedelic');
    }
}
