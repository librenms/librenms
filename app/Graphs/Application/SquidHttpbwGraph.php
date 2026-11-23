<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidHttpbwGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'httpinkb' => new DataSeries($metric, 'httpinkb', __('graph.application.squid.http_in'), style: $style),
            'httpoutkb' => new DataSeries($metric, 'httpoutkb', __('graph.application.squid.http_out'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.kilobytes_per_second'), palette: 'mixed');
    }
}
