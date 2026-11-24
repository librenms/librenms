<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixQstatsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'incomingq' => new DataSeries($metric, 'incomingq', __('graph.application.postfix.incoming')),
            'activeq' => new DataSeries($metric, 'activeq', __('graph.application.postfix.active')),
            'deferredq' => new DataSeries($metric, 'deferredq', __('graph.application.postfix.deferred')),
            'holdq' => new DataSeries($metric, 'holdq', __('graph.application.postfix.hold')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(palette: 'mixed');
    }
}
