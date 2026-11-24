<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class TinydnsErrorsGraph extends ApplicationGraph
{
    /**
     * Record type fields of app-tinydns-<id>.rrd, labeled in upper case
     */
    private const array TYPES = ['notauth', 'notimpl', 'badclass', 'noquery'];

    public function getSeries(): array
    {
        $metric = $this->metric();
        $series = [];
        foreach (self::TYPES as $type) {
            $series[$type] = new DataSeries($metric, $type, strtoupper($type), style: new SeriesStyle(stacked: true));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.queries_per_second'), palette: 'oranges');
    }
}
