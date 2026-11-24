<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PuppetAgentEventsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'events']);

        return [
            'success' => new DataSeries($metric, 'success', __('graph.application.puppet_agent.success')),
            'failure' => new DataSeries($metric, 'failure', __('graph.application.puppet_agent.failure')),
            'total' => new DataSeries($metric, 'total', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.events'), palette: 'psychedelic');
    }
}
