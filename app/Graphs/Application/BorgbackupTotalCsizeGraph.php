<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupTotalCsizeGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___total_csize' : "repos___{$repo}___total_csize";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'total_csize' => new DataSeries($metric, 'data', __('graph.application.borgbackup.cmprssd_size'),
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
