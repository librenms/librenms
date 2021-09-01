<?php

namespace App\View\Components;

class DeviceMiniGraph extends DeviceGraph
{
    public function __construct($device, $type, $start = null, $end = null, $legend = 'no', $width = 275, $height = 100, $class = 'minigraph-image', $loading = 'eager', $absolute_size = 0)
    {
        parent::__construct($device, $type, $start, $end, $legend, $width, $height, $class, $loading, $absolute_size);
    }
}
