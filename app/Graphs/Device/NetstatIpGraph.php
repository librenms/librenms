<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NetstatIpGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return __('graph.device.netstat_ip.title', ['device' => $this->device()->display]);
    }

    public function getSeries(): array
    {
        $metric = $this->metric('netstats-ip');
        $outbound = new SeriesStyle(invert: true);

        return [
            'ipForwDatagrams' => new DataSeries($metric, 'ipForwDatagrams', __('graph.device.netstat_ip.forw_datagrams')),
            'ipInDelivers' => new DataSeries($metric, 'ipInDelivers', __('graph.device.netstat_ip.in_delivers')),
            'ipInReceives' => new DataSeries($metric, 'ipInReceives', __('graph.device.netstat_ip.in_receives')),
            'ipOutRequests' => new DataSeries($metric, 'ipOutRequests', __('graph.device.netstat_ip.out_requests'), style: $outbound),
            'ipInDiscards' => new DataSeries($metric, 'ipInDiscards', __('graph.device.netstat_ip.in_discards')),
            'ipOutDiscards' => new DataSeries($metric, 'ipOutDiscards', __('graph.device.netstat_ip.out_discards'), style: $outbound),
            'ipOutNoRoutes' => new DataSeries($metric, 'ipOutNoRoutes', __('graph.device.netstat_ip.out_no_routes'), style: $outbound),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0);
    }
}
