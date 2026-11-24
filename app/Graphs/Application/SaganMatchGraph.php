<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SaganMatchGraph extends ApplicationGraph
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
            'match' => new DataSeries($metric, 'match', __('graph.application.sagan.matched')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.sagan.matched'), palette: 'psychedelic');
    }
}
