<?php

// One line per cgroup for a single resource, stall type, and average, for comparing cgroups.
//
// $vars['resource'] :: cpu, io, memory, or irq
// $vars['stall_type'] :: some or full
// $vars['average'] :: avg10, avg60, avg300, or total. total is graphed as seconds stalled per second.

$resource = in_array($vars['resource'] ?? null, ['cpu', 'io', 'memory', 'irq']) ? $vars['resource'] : 'cpu';
$stall_type = in_array($vars['stall_type'] ?? null, ['some', 'full']) ? $vars['stall_type'] : 'some';
$average = in_array($vars['average'] ?? null, ['avg10', 'avg60', 'avg300', 'total']) ? $vars['average'] : 'avg300';

if ($average === 'total') {
    // total is in microseconds
    $unit_text = 'Secs/Sec';
    $divider = 1000000;
} else {
    $unit_text = 'Percent Stalled';
}

$pressure_lines = [];
$descr_len = 16;
foreach ($app->data['cgroups'] ?? [] as $cgroup) {
    $pressure_lines[] = [
        'rrd_prefix' => 'cgroup___' . $cgroup,
        'resource' => $resource,
        'stall_type' => $stall_type,
        'ds' => $average,
        'descr' => $cgroup,
    ];
    // widen the legend for long cgroup names, within reason
    $descr_len = min(max($descr_len, strlen($cgroup)), 48);
}

require 'linux_pressure-common.inc.php';
