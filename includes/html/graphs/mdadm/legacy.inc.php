<?php

use LibreNMS\Exceptions\RrdGraphException;

/**
 * Return [unit_text, unitlen, bigdescrlen, smalldescrlen, ds] for a legacy metric key.
 */
function mdadm_legacy_metric_config(string $metric): array
{
    return match ($metric) {
        'level' => ['RAID Level',         10, 15, 15, 'level'],
        'size' => ['RAID Size',           10, 15, 15, 'size'],
        'disc_count' => ['Disk Count',          10, 15, 15, 'disc_count'],
        'hotspare_count' => ['Hotspare Disc Count', 20, 15, 15, 'hotspare_count'],
        'degraded' => ['degraded',            10, 15, 15, 'degraded'],
        'sync_speed' => ['Sync Speed (Byte/s)', 20, 15, 15, 'sync_speed'],
        'sync_completed' => ['Sync completed (%)',  20, 15, 15, 'sync_completed'],
        default => throw new RrdGraphException('Unknown metric: ' . $metric),
    };
}

[$unit_text, $unitlen, $bigdescrlen, $smalldescrlen, $rrdVar] = mdadm_legacy_metric_config($vars['metric'] ?? '');

$name = 'mdadm';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

$arrays = isset($vars['array'])
    ? [$vars['array']]
    : Rrd::getRrdApplicationArrays($device, $app->app_id, $name);

$rrd_list = [];
foreach ($arrays as $array) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $array]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $array,
            'ds' => $rrdVar,
        ];
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
