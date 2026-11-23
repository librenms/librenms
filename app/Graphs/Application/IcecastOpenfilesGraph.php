<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class IcecastOpenfilesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'openfiles' => new DataSeries($this->metric(), 'openfiles', __('graph.units.open_files'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 1000, legendTitle: __('graph.units.open_files'));
    }
}
