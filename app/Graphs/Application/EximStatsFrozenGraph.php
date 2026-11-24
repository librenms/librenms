<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class EximStatsFrozenGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'frozen' => new DataSeries($this->metric(), 'frozen', __('graph.application.exim_stats.frozen_emails'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.exim_stats.frozen'), palette: 'mixed');
    }
}
