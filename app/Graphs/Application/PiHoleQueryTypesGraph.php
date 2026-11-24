<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PiHoleQueryTypesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'query_a' => new DataSeries($metric, 'query_a', __('graph.application.pi_hole.a_type'), style: new SeriesStyle(fill: true)),
            'query_aaaa' => new DataSeries($metric, 'query_aaaa', __('graph.application.pi_hole.aaaa_type'), style: new SeriesStyle(fill: true)),
            'query_ptr' => new DataSeries($metric, 'query_ptr', __('graph.application.pi_hole.ptr_type'), style: new SeriesStyle(fill: true)),
            'query_srv' => new DataSeries($metric, 'query_srv', __('graph.application.pi_hole.srv_type'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.queries'));
    }
}
