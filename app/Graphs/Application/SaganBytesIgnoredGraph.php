<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SaganBytesIgnoredGraph extends ApplicationGraph
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
            'bytes_ignored' => new DataSeries($metric, 'bytes_ignored', __('graph.application.sagan.bytes_ignored')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
