<?php

namespace App\Graphs\Device;

use App\Data\Graphing\AbstractSeriesGraph;
use App\Data\TimeSeries\MetricIdentity;
use App\Models\Device;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of one device, viewable by users who can view the device
 */
abstract class DeviceGraph extends AbstractSeriesGraph
{
    public function authorize(): bool
    {
        $device = $this->graphedDevice();

        return $device !== null && $this->canView($device);
    }

    /**
     * @throws NoGraphData
     */
    protected function device(): Device
    {
        return $this->graphedDevice() ?? throw new NoGraphData;
    }

    /**
     * A metric stored for the device
     *
     * @param  array<string, scalar>  $tags
     *
     * @throws NoGraphData
     */
    protected function metric(string $measurement, array $tags = []): MetricIdentity
    {
        return new MetricIdentity($measurement, ['hostname' => $this->device()->hostname, ...$tags]);
    }

    private function graphedDevice(): ?Device
    {
        return $this->requestedDevice() ?? $this->requestedPort()?->device;
    }
}
