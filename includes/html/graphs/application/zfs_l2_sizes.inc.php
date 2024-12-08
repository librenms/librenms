<?php

$name = 'zfs';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'A Size',
        'ds' => 'l2_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'BufC D Asize',
        'ds' => 'l2_bufc_d_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'BufC M Asize',
        'ds' => 'l2_bufc_m_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Log Blk Asize',
        'ds' => 'l2_log_blk_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'MFU Asize',
        'ds' => 'l2_mfu_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'MRU Asize',
        'ds' => 'l2_mru_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Prefetch Asize',
        'ds' => 'l2_prefetch_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Rebuild Asize',
        'ds' => 'l2_rb_asize',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Log Blk Avg Asize',
        'ds' => 'l2_log_blk_avg_as',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'HDR Size',
        'ds' => 'l2_hdr_size',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
