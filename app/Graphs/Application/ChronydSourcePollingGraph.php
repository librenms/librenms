<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydSourcePollingGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'source' => 'required|string'];
    }

    public function getSeries(): array
    {
        $metric = $this->metric(['source' => $this->params->input->string('source')]);

        return [
            'polling_rate' => new DataSeries($metric, 'polling_rate', __('graph.application.chronyd.polling_rate')),
            'last_rx' => new DataSeries($metric, 'last_rx', __('graph.application.chronyd.last_rx')),
            'number_samplepoints' => new DataSeries($metric, 'number_samplepoints', __('graph.application.chronyd.number_sample_pts')),
            'number_runs' => new DataSeries($metric, 'number_runs', __('graph.application.chronyd.number_runs')),
            'span' => new DataSeries($metric, 'span', __('graph.application.chronyd.sample_span')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(palette: 'mixed');
    }
}
