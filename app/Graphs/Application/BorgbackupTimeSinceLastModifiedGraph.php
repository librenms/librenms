<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupTimeSinceLastModifiedGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___time_since_last_modified' : "repos___{$repo}___time_since_last_modified";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'time_since_last_modified' => new DataSeries($metric, 'data', __('graph.application.borgbackup.since_mtime'),
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
