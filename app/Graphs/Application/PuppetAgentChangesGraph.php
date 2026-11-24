<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PuppetAgentChangesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'changes']);

        return [
            'total' => new DataSeries($metric, 'total', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.puppet_agent.changes'), palette: 'psychedelic');
    }
}
