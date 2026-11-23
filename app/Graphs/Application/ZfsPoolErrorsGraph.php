<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolErrorsGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');

        return [
            'checksum' => new DataSeries($this->metric(['stat' => "{$pool}____checksum_errors"]), 'data', __('graph.application.zfs.checksum')),
            'read' => new DataSeries($this->metric(['stat' => "{$pool}____read_errors"]), 'data', __('graph.series.read')),
            'write' => new DataSeries($this->metric(['stat' => "{$pool}____write_errors"]), 'data', __('graph.series.write')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.errors'), palette: 'psychedelic');
    }
}
