<?php

/**
 * LibreNMS Application Graph: storraid_summary
 *
 * Renders overall severity and OK/Warn/Crit counts for controllers, VDs, PDs.
 */
$name = 'storraid';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Count';
$unitlen = 10;
$bigdes = 1;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [];

if (file_exists($rrd_filename)) {
    foreach ([
        'overall_severity' => 'Overall Severity',
        'ctrl_ok' => 'Ctrl OK',
        'ctrl_warn' => 'Ctrl Warn',
        'ctrl_crit' => 'Ctrl Crit',
        'vd_ok' => 'VD OK',
        'vd_warn' => 'VD Warn',
        'vd_crit' => 'VD Crit',
        'pd_ok' => 'PD OK',
        'pd_warn' => 'PD Warn',
        'pd_crit' => 'PD Crit',
        'pd_total_errors' => 'PD Total Errors',
    ] as $ds => $descr) {
        $rrd_list[] = ['filename' => $rrd_filename, 'descr' => $descr, 'ds' => $ds];
    }
} else {
    d_echo('RRD not found: ' . $rrd_filename);
}

require 'includes/html/graphs/generic_multi_line.inc.php';
