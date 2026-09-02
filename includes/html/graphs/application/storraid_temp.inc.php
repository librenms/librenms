<?php

/**
 * LibreNMS graph — storraid temperatures (controllers + physical disks)
 */
$name = 'storraid';

$rrd_list = [];
$app_id = $app->app_id;
$rrd_dir = dirname(Rrd::name($device['hostname'], ['app', $name, $app_id, 'x']));

// ── Controller temperatures ───────────────────────────────────────────────────
$prefix = 'app-' . $name . '-' . $app_id . '-ctrl_';
$files = glob($rrd_dir . '/' . $prefix . '*.rrd') ?: [];
natsort($files);
foreach ($files as $filepath) {
    $file = basename($filepath);
    $label = substr($file, strlen($prefix), -4);  // e.g. "c0"
    $cid = ltrim($label, 'c');
    $rrd_list[] = [
        'filename' => $filepath,
        'descr' => "Ctrl {$cid}",
        'ds' => 'temperature',
    ];
}

// ── Physical disk temperatures ────────────────────────────────────────────────
$prefix = 'app-' . $name . '-' . $app_id . '-pdtemp_';
$files = glob($rrd_dir . '/' . $prefix . '*.rrd') ?: [];
natsort($files);
foreach ($files as $filepath) {
    $file = basename($filepath);
    $label = substr($file, strlen($prefix), -4);  // e.g. "c0_252_0"
    $parts = explode('_', $label, 3);              // ['c0', '252', '0']
    $descr = isset($parts[1], $parts[2])
           ? $parts[1] . ':' . $parts[2]
           : $label;
    $rrd_list[] = [
        'filename' => $filepath,
        'descr' => $descr,
        'ds' => 'temperature',
    ];
}

$colours = 'mixed';
$nototal = 1;
$unit_text = '°C';
$vertical_label = 'Degrees Celsius';
$format = '%8.0lf';

require 'includes/html/graphs/generic_multi_line.inc.php';
