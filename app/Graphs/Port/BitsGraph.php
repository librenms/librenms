<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\TrafficOptions;
use App\Facades\LibrenmsConfig;

class BitsGraph extends PortGraph
{
    public function getSeries(): array
    {
        $metric = $this->port()->metricIdentity('port');
        [$in, $out] = $this->directions('INOCTETS', 'OUTOCTETS');

        return [
            'in' => new DataSeries(
                metric: $metric,
                field: $in,
                description: __('graph.series.in'),
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(SeriesRole::In, fill: true, maxFill: true),
            ),
            'out' => new DataSeries(
                metric: $metric,
                field: $out,
                description: __('graph.series.out'),
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(SeriesRole::Out, fill: true, maxFill: true, invert: true),
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
