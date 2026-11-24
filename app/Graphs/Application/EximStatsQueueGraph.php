<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class EximStatsQueueGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'queue' => new DataSeries($this->metric(), 'queue', __('graph.application.exim_stats.queue_emails'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.exim_stats.queue'), palette: 'mixed');
    }
}
