<?php

$unit_text = 'Percent Stalled';

$pressure_lines = [];
foreach (['some', 'full'] as $stall_type) {
    foreach (['avg10', 'avg60', 'avg300'] as $average) {
        $pressure_lines[] = [
            'resource' => 'irq',
            'stall_type' => $stall_type,
            'ds' => $average,
            'descr' => $stall_type . ' ' . $average,
        ];
    }
}

require 'linux_pressure-common.inc.php';
