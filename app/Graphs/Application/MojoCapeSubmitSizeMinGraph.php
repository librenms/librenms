<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MojoCapeSubmitSizeMinGraph extends ApplicationGraph
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
            'size_min' => new DataSeries($metric, 'size_min', __('graph.application.mojo_cape_submit.size_min'),
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
