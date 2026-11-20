<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

/**
 * Errors and discards per second, each direction a stack
 */
class ErrorsGraph extends PortGraph
{
    public function getSeries(): array
    {
        $metric = $this->port()->metricIdentity('port');
        $alpha = $this->stacked() ? '88' : '';
        [$inErrors, $outErrors] = $this->directions('INERRORS', 'OUTERRORS');
        [$inDiscards, $outDiscards] = $this->directions('INDISCARDS', 'OUTDISCARDS');

        // inverted series stack separately, after the others
        return [
            'in_errors' => new DataSeries($metric, $inErrors, __('graph.port.errors_in'), 'pps', style: new SeriesStyle(color: 'FF3300' . $alpha, stacked: true)),
            'in_discards' => new DataSeries($metric, $inDiscards, __('graph.port.discards_in'), 'pps', style: new SeriesStyle(color: '805080' . $alpha, stacked: true)),
            'out_errors' => new DataSeries($metric, $outErrors, __('graph.port.errors_out'), 'pps', style: new SeriesStyle(color: 'FF6633' . $alpha, stacked: true, invert: true)),
            'out_discards' => new DataSeries($metric, $outDiscards, __('graph.port.discards_out'), 'pps', style: new SeriesStyle(color: 'C0A060' . $alpha, stacked: true, invert: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.port.packets_per_second'));
    }
}
