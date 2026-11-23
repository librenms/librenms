<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupErroredGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___errored' : "repos___{$repo}___errored";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'errored' => new DataSeries($metric, 'data', __('graph.application.borgbackup.errored'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.borgbackup.repos'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
