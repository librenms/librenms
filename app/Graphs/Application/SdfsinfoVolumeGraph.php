<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SdfsinfoVolumeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'files' => new DataSeries($metric, 'files', __('graph.application.sdfsinfo.files'), style: new SeriesStyle(fill: true)),
            'vol_capacity' => new DataSeries($metric, 'vol_capacity', __('graph.application.sdfsinfo.capacity_gb'), style: new SeriesStyle(fill: true)),
            'vol_logic_size' => new DataSeries($metric, 'vol_logic_size', __('graph.application.sdfsinfo.logic_capacity_gb'), style: new SeriesStyle(fill: true)),
            'vol_max_load' => new DataSeries($metric, 'vol_max_load', __('graph.application.sdfsinfo.max_load_percent'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.sdfsinfo.volume'));
    }
}
