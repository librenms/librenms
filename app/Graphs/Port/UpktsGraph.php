<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

/**
 * Unicast packets per second
 */
class UpktsGraph extends PortGraph
{
    public function getSeries(): array
    {
        $metric = $this->port()->metricIdentity('port');
        $alpha = $this->stacked() ? '88' : '';
        [$in, $out] = $this->directions('INUCASTPKTS', 'OUTUCASTPKTS');

        return [
            'in' => new DataSeries($metric, $in, __('graph.series.in'), 'pps',
                style: new SeriesStyle(color: '330033', fill: true, fillColor: 'AA66AA' . $alpha, maxFillColor: 'CC88CC' . $alpha)),
            'out' => new DataSeries($metric, $out, __('graph.series.out'), 'pps',
                style: new SeriesStyle(color: 'FF6600', fill: true, fillColor: 'FFDD88' . $alpha, maxFillColor: 'FFEFAA' . $alpha, invert: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.packets'));
    }
}
