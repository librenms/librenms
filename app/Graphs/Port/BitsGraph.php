<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\TrafficOptions;
use App\Facades\LibrenmsConfig;

class BitsGraph extends PortGraph
{
    public function getSeries(): array
    {
        $metric = $this->port()->metricIdentity('port');
        $stacked = $this->stacked() || ($this->speedZoom() && array_filter($this->port()->getSpeeds()));
        $alpha = $stacked ? '88' : '';
        [$in, $out] = $this->directions('INOCTETS', 'OUTOCTETS');

        return [
            'in' => new DataSeries(
                metric: $metric,
                field: $in,
                description: __('graph.series.in'),
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(color: '608720', fill: true, fillColor: '90B040' . $alpha, maxFillColor: 'D7FFC7' . $alpha),
            ),
            'out' => new DataSeries(
                metric: $metric,
                field: $out,
                description: __('graph.series.out'),
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(color: '606090', fill: true, fillColor: '8080C0' . $alpha, maxFillColor: 'E0E0FF' . $alpha, invert: ! $stacked),
            ),
        ];
    }

    protected function options(): GraphOptions
    {
        [$out, $in] = $this->port()->getSpeeds();
        if ($this->params->inverse) {
            [$in, $out] = [$out, $in];
        }

        return new GraphOptions(legendTitle: 'bps', traffic: new TrafficOptions(
            percentile: (float) LibrenmsConfig::get('percentile_value', 95),
            highestPercentile: (bool) LibrenmsConfig::get('rrdgraph_real_percentile'),
            speeds: ['in' => $in, 'out' => $out],
            speedZoom: $this->speedZoom(),
        ));
    }

    private function speedZoom(): bool
    {
        return $this->params->input->bool('port_speed_zoom') ?? (bool) LibrenmsConfig::get('graphs.port_speed_zoom');
    }
}
