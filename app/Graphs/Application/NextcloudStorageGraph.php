<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class NextcloudStorageGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'free' => new DataSeries($this->metric(['stat' => 'free']), 'data', __('graph.series.free')),
            'used' => new DataSeries($this->metric(['stat' => 'used']), 'data', __('graph.series.used')),
            'total' => new DataSeries($this->metric(['stat' => 'total']), 'data', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
