<?php

namespace App\Graphs\Device;

use App\Data\Graphing\AbstractSeriesGraph;

/**
 * A graph of one device, viewable by users who can view the device
 */
abstract class DeviceGraph extends AbstractSeriesGraph
{
    public function authorize(): bool
    {
        $device = $this->getDevice();

        return $device !== null && $this->allows('view', $device);
    }
}
