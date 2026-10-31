<?php

namespace App\Graphs\Device;

use App\Data\Graphing\AbstractRrdGraph;
use App\Data\Graphing\Builders\MultiLineGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

class NetstatIpGraph extends AbstractRrdGraph implements GraphDataInterface
{
    public function authorize(): bool
    {
        $device = $this->getDevice();

        return $device !== null && $this->allows('view', $device);
    }

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

        return [
            'ipForwDatagrams' => new DataSeries($metric, 'ipForwDatagrams', 'Fwd Datagrams'),
            'ipInDelivers' => new DataSeries($metric, 'ipInDelivers', 'In Delivers'),
            'ipInReceives' => new DataSeries($metric, 'ipInReceives', 'In Receives'),
            'ipOutRequests' => new DataSeries($metric, 'ipOutRequests', 'Out Requests'),
            'ipInDiscards' => new DataSeries($metric, 'ipInDiscards', 'In Discards'),
            'ipOutDiscards' => new DataSeries($metric, 'ipOutDiscards', 'Out Discards'),
            'ipOutNoRoutes' => new DataSeries($metric, 'ipOutNoRoutes', 'Out No Routes'),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0);
    }

    protected function rrdDefinition(): array
    {
        return MultiLineGraphBuilder::data($this, $this->getValidator())
            ->setSeriesOptions(['ipOutRequests', 'ipOutDiscards', 'ipOutNoRoutes'], invert: true)
            ->build($this->params);
    }
}
