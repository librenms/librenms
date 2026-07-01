<?php

use App\Facades\Rrd;
use App\Models\MdadmArray;

/**
 * Shared array-level graph builder for the v3 mdadm app.
 *
 * Type files set $unit_text, $unitlen, $datasets, $graph_metric_title and
 * optional $scale_min/$scale_max before requiring this. Reads the array RRD
 * keyed by the stable array UUID: ['app', 'mdadm', <app_id>, <array uuid>].
 */
$name = 'mdadm';
$bigdescrlen = 10;
$smalldescrlen = 10;
$colours = 'mixed';
$dostack = 0;
$printtotal = 0;
$transparency = 15;

$arrayParam = $vars['array'] ?? '';
$dbArray = $arrayParam !== ''
    ? MdadmArray::where('app_id', $app->app_id)
        ->where(function ($q) use ($arrayParam): void {
            $q->where('uuid', $arrayParam)->orWhere('array_name', $arrayParam)->orWhere('md_id', $arrayParam);
        })
        ->first()
    : null;

$rrd_filename = $dbArray !== null ? Rrd::name($device['hostname'], ['app', $name, $app->app_id, $dbArray->uuid]) : '';

if ($dbArray !== null) {
    $graph_title = $dbArray->graphLabel() . ' :: ' . ($graph_metric_title ?? '');
}

$rrd_list = [];
if ($rrd_filename !== '' && Rrd::checkRrdExists($rrd_filename)) {
    foreach ($datasets as $spec) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $spec['descr'],
            'ds' => $spec['ds'],
        ];
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
