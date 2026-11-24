<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyUniqueDomainsNpGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'unique_domains_np' => new DataSeries($this->metric(), 'unique_domains_np', __('graph.application.privoxy.unique_domains_np'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.unique_domains_np'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
