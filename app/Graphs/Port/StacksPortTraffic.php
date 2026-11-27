<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;
use App\Models\Port;
use Closure;

/**
 * Traffic of several ports stacked on each other, with their totals in the legend
 */
trait StacksPortTraffic
{
    /**
     * @param  iterable<Port>  $ports
     * @param  Closure(Port): string  $label
     * @return array<string, DataSeries|SeriesSum>
     */
    protected function portTrafficSeries(iterable $ports, Closure $label): array
    {
        $series = [];
        $in = [];
        $out = [];
        [$inField, $outField] = $this->params->inverse ? ['OUTOCTETS', 'INOCTETS'] : ['INOCTETS', 'OUTOCTETS'];
        foreach ($ports as $port) {
            $metric = $port->metricIdentity('port');
            $portLabel = $label($port);
            $in[] = "in_$port->port_id";
            $out[] = "out_$port->port_id";
            $series["in_$port->port_id"] = new DataSeries($metric, $inField, $portLabel . ' ' . __('graph.series.in'), 'bps', 8,
                new SeriesStyle(SeriesRole::In, stacked: true));
            $series["out_$port->port_id"] = new DataSeries($metric, $outField, $portLabel . ' ' . __('graph.series.out'), 'bps', 8,
                new SeriesStyle(SeriesRole::Out, stacked: true, invert: true));
        }

        if ($series === []) {
            return [];
        }

        // totals are drawn on the side of the axis of their ports
        $series['in'] = new SeriesSum($in, __('graph.series.total_in'), 'bps', new SeriesStyle(legendOnly: true, percentileLines: true));
        $series['out'] = new SeriesSum($out, __('graph.series.total_out'), 'bps', new SeriesStyle(invert: true, legendOnly: true, percentileLines: true));
        $series['aggregate'] = new SeriesSum(['in', 'out'], __('graph.series.aggregate'), 'bps', new SeriesStyle(legendOnly: true));

        return $series;
    }

    protected function options(): GraphOptions
    {
        return GraphOptions::forTraffic(series: ['in', 'out']);
    }
}
