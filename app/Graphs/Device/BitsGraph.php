<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;
use App\Data\Graphing\TrafficOptions;
use App\Facades\LibrenmsConfig;
use App\Models\Port;

/**
 * Traffic of all ports of a device, each port stacked on the others
 */
class BitsGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->device()->display . '::bits';
    }

    public function getSeries(): array
    {
        $device = $this->device();

        $series = [];
        $in = [];
        $out = [];
        foreach ($device->ports()->isNotDeleted()->where('disabled', 0)->orderBy('ifIndex')->get() as $port) {
            $port->setRelation('device', $device);
            if (! $this->isTraffic($port)) {
                continue;
            }

            $metric = $port->metricIdentity('port');
            $label = $port->getShortLabel();
            $in[] = "in_$port->port_id";
            $out[] = "out_$port->port_id";
            $series["in_$port->port_id"] = new DataSeries($metric, 'INOCTETS', $label . ' ' . __('graph.series.in'), 'bps', 8,
                new SeriesStyle(SeriesRole::In, stacked: true));
            $series["out_$port->port_id"] = new DataSeries($metric, 'OUTOCTETS', $label . ' ' . __('graph.series.out'), 'bps', 8,
                new SeriesStyle(SeriesRole::Out, stacked: true, invert: true));
        }

        if ($series === []) {
            return [];
        }

        // the device totals, their percentiles and previous period are drawn on the side of the axis of their ports
        $series['in'] = new SeriesSum($in, __('graph.series.total_in'), 'bps', new SeriesStyle(legendOnly: true, percentileLines: true));
        $series['out'] = new SeriesSum($out, __('graph.series.total_out'), 'bps', new SeriesStyle(invert: true, legendOnly: true, percentileLines: true));
        $series['aggregate'] = new SeriesSum(['in', 'out'], __('graph.series.aggregate'), 'bps', new SeriesStyle(legendOnly: true));

        return $series;
    }

    protected function options(): GraphOptions
    {
        $percentile = (float) LibrenmsConfig::get('percentile_value', 95);

        return new GraphOptions(
            legendTitle: 'bps',
            traffic: new TrafficOptions(
                highestPercentile: LibrenmsConfig::get('rrdgraph_real_percentile') ? $percentile : null,
                series: ['in', 'out'],
            ),
            percentiles: [$percentile],
        );
    }

    /**
     * Whether the port counts toward the traffic of the device, configured by device_traffic_iftype and device_traffic_descr
     */
    private function isTraffic(Port $port): bool
    {
        // ASA (at least in multicontext) reports layer 3 interfaces as l2vlan, or as propVirtual in 9.16 (like etherchannels),
        // but not the physical ones. Filtering those would leave the graph empty.
        $asaTypes = $port->device?->os === 'asa' ? ['/virtual/', '/l2vlan/'] : [];

        $ignoredType = array_any((array) LibrenmsConfig::get('device_traffic_iftype', []),
            fn ($type) => ! in_array($type, $asaTypes) && preg_match($type . 'i', (string) $port->ifType));
        $ignoredDescr = array_any((array) LibrenmsConfig::get('device_traffic_descr', []),
            fn ($descr) => preg_match($descr . 'i', (string) $port->ifDescr) || preg_match($descr . 'i', (string) $port->ifName));

        return ! $ignoredType && ! $ignoredDescr;
    }
}
