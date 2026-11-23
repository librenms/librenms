<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolSyncqReadGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');

        return [
            'active' => new DataSeries($this->metric(['stat' => "{$pool}____syncq_read_a"]), 'data', __('graph.application.zfs.active')),
            'pending' => new DataSeries($this->metric(['stat' => "{$pool}____syncq_read_p"]), 'data', __('graph.application.zfs.pending')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops'), palette: 'psychedelic');
    }
}
