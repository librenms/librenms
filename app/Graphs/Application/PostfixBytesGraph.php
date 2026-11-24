<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixBytesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'bytesr' => new DataSeries($metric, 'bytesr', __('graph.series.received')),
            'bytesd' => new DataSeries($metric, 'bytesd', __('graph.application.postfix.delivered')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'mixed');
    }
}
