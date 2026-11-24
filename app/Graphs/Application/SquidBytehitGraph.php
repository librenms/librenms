<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidBytehitGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'reqbyteratio1' => new DataSeries($metric, 'reqbyteratio1', __('graph.application.squid.1_minute'), style: $style),
            'reqbyteratio5' => new DataSeries($metric, 'reqbyteratio5', __('graph.application.squid.5_minute'), style: $style),
            'reqbyteratio60' => new DataSeries($metric, 'reqbyteratio60', __('graph.application.squid.60_minute'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.squid.byte_hit_ratio'), palette: 'mixed');
    }
}
