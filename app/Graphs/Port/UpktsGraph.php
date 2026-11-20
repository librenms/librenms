<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

/**
 * Unicast packets per second
 */
class UpktsGraph extends PortGraph
{
    public function getSeries(): array
    {
        $metric = $this->port()->metricIdentity('port');
        [$in, $out] = $this->directions('INUCASTPKTS', 'OUTUCASTPKTS');

        return [
            'in' => new DataSeries($metric, $in, __('graph.series.in'), 'pps',
                style: new SeriesStyle(SeriesRole::PacketsIn, fill: true, maxFill: true)),
            'out' => new DataSeries($metric, $out, __('graph.series.out'), 'pps',
                style: new SeriesStyle(SeriesRole::PacketsOut, fill: true, maxFill: true, invert: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.packets'));
    }
}
