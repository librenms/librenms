<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SdfsinfoRatesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'dedup_rate' => new DataSeries($metric, 'dedup_rate', __('graph.application.sdfsinfo.dedup_rate_percent'), style: new SeriesStyle(fill: true)),
            'actual_savings' => new DataSeries($metric, 'actual_savings', __('graph.application.sdfsinfo.savings_percent'), style: new SeriesStyle(fill: true)),
            'comp_rate' => new DataSeries($metric, 'comp_rate', __('graph.application.sdfsinfo.compression_percent'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.sdfsinfo.rates'));
    }
}
