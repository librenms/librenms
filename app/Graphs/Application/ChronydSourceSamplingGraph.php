<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydSourceSamplingGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'source' => 'required|string'];
    }

    public function getSeries(): array
    {
        $metric = $this->metric(['source' => $this->params->input->string('source')]);

        return [
            'adjusted_offset' => new DataSeries($metric, 'adjusted_offset', __('graph.application.chronyd.adjusted')),
            'measured_offset' => new DataSeries($metric, 'measured_offset', __('graph.application.chronyd.measured')),
            'offset' => new DataSeries($metric, 'offset', __('graph.application.chronyd.estimated')),
            'estimated_error' => new DataSeries($metric, 'estimated_error', __('graph.application.chronyd.est_error')),
            'stddev' => new DataSeries($metric, 'stddev', __('graph.application.chronyd.std_dev')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.seconds'), palette: 'mixed');
    }
}
