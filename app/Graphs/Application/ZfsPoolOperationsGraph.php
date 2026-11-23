<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolOperationsGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');

        return [
            'read' => new DataSeries($this->metric(['stat' => "{$pool}____operations_r"]), 'data', __('graph.series.read')),
            'write' => new DataSeries($this->metric(['stat' => "{$pool}____operations_w"]), 'data', __('graph.series.write')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops'), palette: 'psychedelic');
    }
}
