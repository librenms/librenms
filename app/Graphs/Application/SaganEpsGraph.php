<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SaganEpsGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'sinstance' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $instance = $this->params->input->string('sinstance');
        $metric = $instance === null ? $this->metric() : $this->metric(['instance' => $instance]);

        return [
            'eps' => new DataSeries($metric, 'eps', __('graph.application.sagan.eps')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.events_per_second'), palette: 'psychedelic');
    }
}
