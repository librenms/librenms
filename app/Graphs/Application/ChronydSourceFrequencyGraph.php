<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydSourceFrequencyGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'source' => 'required|string'];
    }

    public function getSeries(): array
    {
        $metric = $this->metric(['source' => $this->params->input->string('source')]);

        return [
            'frequency' => new DataSeries($metric, 'frequency', __('graph.application.chronyd.estimated')),
            'frequency_skew' => new DataSeries($metric, 'frequency_skew', __('graph.application.chronyd.est_error')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.chronyd.seconds_ppm'), palette: 'mixed');
    }
}
