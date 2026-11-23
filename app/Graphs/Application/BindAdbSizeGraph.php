<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindAdbSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'adb']);

        return [
            'ahts' => new DataSeries($metric, 'ahts', __('graph.application.bind.address')),
            'nhts' => new DataSeries($metric, 'nhts', __('graph.application.bind.name')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.table_size'), palette: 'psychedelic');
    }
}
