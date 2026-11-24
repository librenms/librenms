<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidObjcountGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'numobjcount' => new DataSeries($metric, 'numobjcount', __('graph.application.squid.stored'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.objects'), palette: 'mixed');
    }
}
