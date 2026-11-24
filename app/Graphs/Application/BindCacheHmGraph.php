<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindCacheHmGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'cache']);

        return [
            'ch' => new DataSeries($metric, 'ch', __('graph.application.bind.hits')),
            'cm' => new DataSeries($metric, 'cm', __('graph.application.bind.misses')),
            'chfq' => new DataSeries($metric, 'chfq', __('graph.application.bind.hits_frm_qry')),
            'cmfq' => new DataSeries($metric, 'cmfq', __('graph.application.bind.misses_frm_qry')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
