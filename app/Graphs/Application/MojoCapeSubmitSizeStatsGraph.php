<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class MojoCapeSubmitSizeStatsGraph extends ApplicationGraph
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
            'max' => new DataSeries($metric, 'size_max', __('graph.application.mojo_cape_submit.max')),
            'mean' => new DataSeries($metric, 'size_mean', __('graph.application.mojo_cape_submit.mean')),
            'median' => new DataSeries($metric, 'size_median', __('graph.application.mojo_cape_submit.median')),
            'mode' => new DataSeries($metric, 'size_mode', __('graph.application.mojo_cape_submit.mode')),
            'min' => new DataSeries($metric, 'size_min', __('graph.application.mojo_cape_submit.min')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
