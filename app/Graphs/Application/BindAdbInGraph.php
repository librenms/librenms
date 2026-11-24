<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindAdbInGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'adb']);

        return [
            'aiht' => new DataSeries($metric, 'aiht', __('graph.application.bind.addresses')),
            'niht' => new DataSeries($metric, 'niht', __('graph.application.bind.names')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.in_hash_table'), palette: 'psychedelic');
    }
}
