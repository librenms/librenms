<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SaganAfterGraph extends ApplicationGraph
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
            'after' => new DataSeries($metric, 'after', __('graph.application.sagan.after')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.sagan.after'), palette: 'psychedelic');
    }
}
