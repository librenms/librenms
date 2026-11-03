<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;

class NetstatIpGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->getDevice()?->display . ' :: IP NetStats';
    }

    public function getSeries(): array
    {
        $device = $this->getDevice();

        if ($device === null) {
            return [];
        }

        $metric = new MetricIdentity($device->device_id, 'netstats-ip');
        $outbound = new SeriesStyle(invert: true);

        return [
            'ipForwDatagrams' => new DataSeries($metric, 'ipForwDatagrams', 'Fwd Datagrams'),
            'ipInDelivers' => new DataSeries($metric, 'ipInDelivers', 'In Delivers'),
            'ipInReceives' => new DataSeries($metric, 'ipInReceives', 'In Receives'),
            'ipOutRequests' => new DataSeries($metric, 'ipOutRequests', 'Out Requests', style: $outbound),
            'ipInDiscards' => new DataSeries($metric, 'ipInDiscards', 'In Discards'),
            'ipOutDiscards' => new DataSeries($metric, 'ipOutDiscards', 'Out Discards', style: $outbound),
            'ipOutNoRoutes' => new DataSeries($metric, 'ipOutNoRoutes', 'Out No Routes', style: $outbound),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0);
    }
}
