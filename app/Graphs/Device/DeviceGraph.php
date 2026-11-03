<?php

namespace App\Graphs\Device;

use App\Data\Graphing\AbstractSeriesGraph;
use App\Models\Device;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of one device, viewable by users who can view the device
 */
abstract class DeviceGraph extends AbstractSeriesGraph
{
    public function authorize(): bool
    {
        $device = $this->getDevice();

        return $device !== null && $this->canView($device);
    }

    /**
     * @throws NoGraphData
     */
    protected function device(): Device
    {
        return $this->getDevice() ?? throw new NoGraphData;
    }
}
