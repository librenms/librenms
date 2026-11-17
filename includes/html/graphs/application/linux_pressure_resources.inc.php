<?php

// One line per resource for a single stall type and average, for comparing resources.
//
// $vars['stall_type'] :: some or full. irq only has full, so it is only drawn for full.
// $vars['average'] :: avg10, avg60, or avg300
// $vars['cgroup'] :: optional, see linux_pressure-common.inc.php

$stall_type = in_array($vars['stall_type'] ?? null, ['some', 'full']) ? $vars['stall_type'] : 'some';
$average = in_array($vars['average'] ?? null, ['avg10', 'avg60', 'avg300']) ? $vars['average'] : 'avg60';

$unit_text = 'Percent Stalled';

$pressure_lines = [];
foreach (['cpu', 'io', 'memory', 'irq'] as $resource) {
    $pressure_lines[] = [
        'resource' => $resource,
        'stall_type' => $stall_type,
        'ds' => $average,
        'descr' => $resource,
    ];
}

require 'linux_pressure-common.inc.php';
