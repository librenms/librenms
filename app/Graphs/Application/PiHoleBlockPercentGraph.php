<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PiHoleBlockPercentGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'ads_percentage' => new DataSeries($metric, 'ads_percentage', __('graph.application.pi_hole.ads_blocked_percent'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: __('graph.application.pi_hole.stats'));
    }
}
