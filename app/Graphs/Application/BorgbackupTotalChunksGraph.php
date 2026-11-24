<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class BorgbackupTotalChunksGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'borgrepo' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $repo = $this->params->input->string('borgrepo');
        $stat = $repo === null ? 'totals___total_chunks' : "repos___{$repo}___total_chunks";
        $metric = $this->metric(['stat' => $stat]);

        return [
            'total_chunks' => new DataSeries($metric, 'data', __('graph.application.borgbackup.total_chunks'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.borgbackup.chunks'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
