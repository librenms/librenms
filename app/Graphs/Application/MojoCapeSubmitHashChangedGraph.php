<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MojoCapeSubmitHashChangedGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'slug' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $slug = $this->params->input->string('slug');
        $metric = $slug === null ? $this->metric() : $this->metric(['slug' => "slugs___-___{$slug}"]);

        return [
            'hash_changed' => new DataSeries($metric, 'hash_changed', __('graph.application.mojo_cape_submit.change_count'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.mojo_cape_submit.changed_hashes'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
