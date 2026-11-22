<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\Threshold;
use App\Data\Graphing\TrafficOptions;
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Number;

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
                style: new SeriesStyle(SeriesRole::In, fill: true, maxFill: true, percentileLines: true),
            ),
            'out' => new DataSeries(
                metric: $metric,
                field: $out,
                description: __('graph.series.out'),
                unit: 'bps',
                scale: 8,
                style: new SeriesStyle(SeriesRole::Out, fill: true, maxFill: true, invert: true, percentileLines: true),
            ),
        ];
    }

    protected function options(): GraphOptions
    {
        [$out, $in] = $this->port()->getSpeeds();
        if ($this->params->inverse) {
            [$in, $out] = [$out, $in];
        }
        $shown = $this->showsPortSpeed();

        $percentile = (float) LibrenmsConfig::get('percentile_value', 95);

        return new GraphOptions(
            legendTitle: 'bps',
            traffic: new TrafficOptions(
                highestPercentile: LibrenmsConfig::get('rrdgraph_real_percentile') ? $percentile : null,
                speedZoom: $shown && ($in || $out),
            ),
            percentiles: [$percentile],
            thresholds: $this->portSpeeds($in, $out, $shown),
        );
    }

    /**
     * One line for both directions at the same speed, otherwise a line for each that has one
     *
     * @return list<Threshold>
     */
    private function portSpeeds(int $in, int $out, bool $shown): array
    {
        $label = fn (string $key, int $speed) => __($key, ['speed' => Number::formatSi($speed, 2, 0, 'bps')]);

        if ($in === $out) {
            return $in ? [new Threshold($in, $label('graph.legend.port_speed', $in), SeriesRole::PortSpeed, $shown, mirror: true)] : [];
        }

        return array_values(array_filter([
            $in ? new Threshold($in, $label('graph.legend.port_speed_in', $in), SeriesRole::PortSpeed, $shown) : null,
            $out ? new Threshold($out, $label('graph.legend.port_speed_out', $out), SeriesRole::PortSpeed, $shown, invert: true) : null,
        ]));
    }

    /**
     * Shown when zoomed to the port speed, as the request or the site sets it
     */
    private function showsPortSpeed(): bool
    {
        return $this->params->input->bool('port_speed_zoom') ?? (bool) LibrenmsConfig::get('graphs.port_speed_zoom');
    }
}
