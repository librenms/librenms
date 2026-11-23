<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupLockedForGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___locked_for' : "repos___{$repo}___locked_for";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'locked_for' => new DataSeries($metric, 'data', __('graph.application.borgbackup.locked_for'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.seconds'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
