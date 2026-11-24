<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PortactivityTotalsGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'port' => 'required|string'];
    }

    public function getSeries(): array
    {
        $metric = $this->metric(['port' => $this->params->input->string('port')]);

        return [
            'total' => new DataSeries($metric, 'total_conns', __('graph.series.total')),
            'from' => new DataSeries($metric, 'total_from', __('graph.application.portactivity.from')),
            'to' => new DataSeries($metric, 'total_to', __('graph.application.portactivity.to')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.connections'), palette: 'psychedelic');
    }
}
