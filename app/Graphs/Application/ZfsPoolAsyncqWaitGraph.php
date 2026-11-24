<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolAsyncqWaitGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');

        return [
            'write' => new DataSeries($this->metric(['stat' => "{$pool}____asyncq_wait_w"]), 'data', __('graph.series.write'), scale: 1 / 1000000),
            'read' => new DataSeries($this->metric(['stat' => "{$pool}____asyncq_wait_r"]), 'data', __('graph.series.read'), scale: 1 / 1000000),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds'), palette: 'psychedelic');
    }
}
