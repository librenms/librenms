<?php

// total is in microseconds, so this makes it seconds stalled per second
$unit_text = 'Secs/Sec';
$divider = 1000000;

$pressure_lines = [];
foreach (['cpu', 'io', 'memory', 'irq'] as $resource) {
    foreach (['some', 'full'] as $stall_type) {
        $pressure_lines[] = [
            'resource' => $resource,
            'stall_type' => $stall_type,
            'ds' => 'total',
            'descr' => $resource . ' ' . $stall_type,
        ];
    }
}

require 'linux_pressure-common.inc.php';
