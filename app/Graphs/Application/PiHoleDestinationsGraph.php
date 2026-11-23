<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PiHoleDestinationsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'queries_forwarded' => new DataSeries($metric, 'queries_forwarded', __('graph.application.pi_hole.queries_forwarded'), style: new SeriesStyle(fill: true)),
            'queries_cached' => new DataSeries($metric, 'queries_cached', __('graph.application.pi_hole.queries_cached'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.pi_hole.stats'));
    }
}
