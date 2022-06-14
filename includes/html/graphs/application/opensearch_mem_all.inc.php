<?php

$name = 'opensearch';
$app_id = $app['app_id'];
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Query Cache',
        'ds'       => 'tqc_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Request Cache',
        'ds'       => 'trc_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Fielddata',
        'ds'       => 'tfd_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Segments',
        'ds'       => 'tseg_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Terms',
        'ds'       => 'tseg_terms_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Fields',
        'ds'       => 'tseg_fields_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg.Term.Vectors',
        'ds'       => 'tseg_tvector_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Norms',
        'ds'       => 'tseg_norms_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Points',
        'ds'       => 'tseg_points_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Doc Vals',
        'ds'       => 'tseg_docval_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg.Ind.Wrt.',
        'ds'       => 'tseg_indwrt_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Ver. Map.',
        'ds'       => 'tseg_vermap_size',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Seg. Fixed Bit Set',
        'ds'       => 'tseg_fbs_size',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
