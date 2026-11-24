<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidReqhitGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'reqhitratio1' => new DataSeries($metric, 'reqhitratio1', __('graph.application.squid.1_minute'), style: $style),
            'reqhitratio5' => new DataSeries($metric, 'reqhitratio5', __('graph.application.squid.5_minute'), style: $style),
            'reqhitratio60' => new DataSeries($metric, 'reqhitratio60', __('graph.application.squid.60_minute'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.squid.hit_ratio'), palette: 'mixed');
    }
}
