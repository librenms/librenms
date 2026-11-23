<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolScrubWaitGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');
        $metric = $this->metric(['stat' => "{$pool}____scrub_wait"]);

        return [
            'scrub' => new DataSeries($metric, 'data', __('graph.application.zfs.scrub'), scale: 1 / 1000000),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds'), palette: 'psychedelic');
    }
}
