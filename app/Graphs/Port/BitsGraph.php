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
        $stacked = LibrenmsConfig::get('webui.graph_stacked') || ($this->speedZoom() && array_filter($this->port()->getSpeeds()));
        $alpha = $stacked ? '88' : '';

        return [
            'in' => new DataSeries(
                metric: $metric,
                field: $this->params->inverse ? 'OUTOCTETS' : 'INOCTETS',
                description: 'In',
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(color: '608720', fill: true, fillColor: '90B040' . $alpha, maxFillColor: 'D7FFC7' . $alpha),
            ),
            'out' => new DataSeries(
                metric: $metric,
                field: $this->params->inverse ? 'INOCTETS' : 'OUTOCTETS',
                description: 'Out',
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
