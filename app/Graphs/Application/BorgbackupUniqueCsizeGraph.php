<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupUniqueCsizeGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___unique_csize' : "repos___{$repo}___unique_csize";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'unique_csize' => new DataSeries($metric, 'data', __('graph.application.borgbackup.dedup_size'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.bytes'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
