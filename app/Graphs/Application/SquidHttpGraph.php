<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidHttpGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'protoclienthttpreq' => new DataSeries($metric, 'protoclienthttpreq', __('graph.application.squid.requests'), style: $style),
            'httphits' => new DataSeries($metric, 'httphits', __('graph.application.squid.hits'), style: $style),
            'httperrors' => new DataSeries($metric, 'httperrors', __('graph.application.squid.errs_to_clnt'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(palette: 'mixed');
    }
}
