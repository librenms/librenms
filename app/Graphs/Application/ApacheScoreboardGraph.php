<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ApacheScoreboardGraph extends ApplicationGraph
{
    /**
     * Scoreboard field => translation key of its label
     */
    private const array STATES = [
        'sb_reading' => 'graph.application.apache.reading',
        'sb_writing' => 'graph.application.apache.writing',
        'sb_wait' => 'graph.application.apache.waiting',
        'sb_start' => 'graph.application.apache.starting',
        'sb_keepalive' => 'graph.application.apache.keepalive',
        'sb_dns' => 'graph.application.apache.dns',
        'sb_closing' => 'graph.application.apache.closing',
        'sb_logging' => 'graph.application.apache.logging',
        'sb_graceful' => 'graph.application.apache.graceful',
        'sb_idle' => 'graph.application.apache.idle',
    ];

    public function getSeries(): array
    {
        $metric = $this->metric();
        $series = [];
        foreach (self::STATES as $field => $label) {
            $series[$field] = new DataSeries($metric, $field, __($label), style: new SeriesStyle(stacked: true));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.workers'), palette: 'mixed');
    }
}
