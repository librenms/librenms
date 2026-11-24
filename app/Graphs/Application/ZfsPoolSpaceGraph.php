<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsPoolSpaceGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'pool' => 'required|string'];
    }

    public function getSeries(): array
    {
        $pool = $this->params->input->string('pool');
        $metric = $this->metric(['pool' => $pool]);

        return [
            'size' => new DataSeries($metric, 'size', __('graph.application.zfs.size')),
            'alloc' => new DataSeries($metric, 'alloc', __('graph.application.zfs.allocated')),
            'free' => new DataSeries($metric, 'free', __('graph.series.free')),
            'expandsz' => new DataSeries($metric, 'expandsz', __('graph.application.zfs.expand_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
