<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolTrimWaitGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');
        $metric = $this->metric(['stat' => "{$pool}____trim_wait"]);

        return [
            'trim_wait' => new DataSeries($metric, 'data', __('graph.application.zfs.trim_wait'), scale: 1 / 1000000),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds'), palette: 'psychedelic');
    }
}
